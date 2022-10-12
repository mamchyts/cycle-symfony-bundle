<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Security;

use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\{ManagerRegistry, ObjectManager, ObjectRepository};
use Symfony\Component\Security\Core\Exception\{UnsupportedUserException, UserNotFoundException};
use Symfony\Component\Security\Core\User\{PasswordAuthenticatedUserInterface, PasswordUpgraderInterface, UserInterface, UserProviderInterface};

class EntityUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private ManagerRegistry $registry;
    private ?string $managerName;
    private string $classOrAlias;
    private string $class;
    private ?string $property;

    public function __construct()
    {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $repository = $this->getRepository();
        if ($this->property !== null) {
            $user = $repository->findOneBy([$this->property => $identifier]);
        } else {
            if (!$repository instanceof UserLoaderInterface) {
                throw new \InvalidArgumentException(sprintf('You must either make the "%s" entity Doctrine Repository ("%s") implement "Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface" or set the "property" option in the corresponding entity provider configuration.', $this->classOrAlias, get_debug_type($repository)));
            }

            $user = $repository->loadUserByIdentifier($identifier);
        }

        if ($user === null) {
            $e = new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
            $e->setUserIdentifier($identifier);

            throw $e;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $repository = $this->getRepository();
        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            // The user must be reloaded via the primary key as all other data
            // might have changed without proper persistence in the database.
            // That's the case when the user has been changed by a form with
            // validation errors.
            if (!$id = $this->getClassMetadata()->getIdentifierValues($user)) {
                throw new \InvalidArgumentException('You cannot refresh a user from the EntityUserProvider that does not contain an identifier. The user object has to be serialized with its own identifier mapped by Doctrine.');
            }

            $refreshedUser = $repository->find($id);
            if ($refreshedUser === null) {
                $e = new UserNotFoundException('User with id ' . json_encode($id) . ' not found.');
                $e->setUserIdentifier(json_encode($id));

                throw $e;
            }
        }

        return $refreshedUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass(string $class): bool
    {
        return $class === $this->getClass() || is_subclass_of($class, $this->getClass());
    }

    /**
     * {@inheritdoc}
     *
     * @final
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        $class = $this->getClass();
        if (!$user instanceof $class) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $repository = $this->getRepository();
        if ($repository instanceof PasswordUpgraderInterface) {
            $repository->upgradePassword($user, $newHashedPassword);
        }
    }

    private function getObjectManager(): ObjectManager
    {
        return $this->registry->getManager($this->managerName);
    }

    private function getRepository(): ObjectRepository
    {
        return $this->getObjectManager()->getRepository($this->classOrAlias);
    }

    private function getClass(): string
    {
        if (!isset($this->class)) {
            $class = $this->classOrAlias;

            if (str_contains($class, ':')) {
                $class = $this->getClassMetadata()->getName();
            }

            $this->class = $class;
        }

        return $this->class;
    }

    private function getClassMetadata(): ClassMetadata
    {
        return $this->getObjectManager()->getClassMetadata($this->classOrAlias);
    }
}
