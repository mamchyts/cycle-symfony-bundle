<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Security;

use Cycle\ORM\{ORMInterface, RepositoryInterface};
use Cycle\SymfonyBundle\Exception\AbstractException;
use Symfony\Component\Security\Core\Exception\{UnsupportedUserException, UserNotFoundException};
use Symfony\Component\Security\Core\User\{PasswordAuthenticatedUserInterface, PasswordUpgraderInterface, UserInterface, UserProviderInterface};

class EntityUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private ORMInterface $orm,
        private string $class,
        private string $property,
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $repository = $this->getRepository();

        /** @var UserInterface|null */
        $user = $repository->findOne([$this->property => $identifier]);

        if ($user === null) {
            $e = new UserNotFoundException('User with identifier "' . $identifier . '" not found');
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
        if (!$user instanceof $this->class) {
            throw new UnsupportedUserException('Instances of "' . get_debug_type($user) . '" are not supported.');
        }

        $repository = $this->getRepository();
        if ($repository instanceof UserProviderInterface) {
            $refreshedUser = $repository->refreshUser($user);
        } else {
            // The user must be reloaded via the primary key as all other data
            // might have changed without proper persistence in the database.
            // That's the case when the user has been changed by a form with
            // validation errors.
            $id = $this->getUserPkValue($user);

            /** @var UserInterface|null */
            $refreshedUser = $repository->findByPK($id);
            if ($refreshedUser === null) {
                $e = new UserNotFoundException('User with id "' . $id . '" not found');
                $e->setUserIdentifier((string) $id);

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
        return $class === $this->class || is_subclass_of($class, $this->class);
    }

    /**
     * {@inheritdoc}
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof $this->class) {
            throw new UnsupportedUserException('Instances of "' . get_debug_type($user) . '" are not supported');
        }

        $repository = $this->getRepository();
        if ($repository instanceof PasswordUpgraderInterface) {
            $repository->upgradePassword($user, $newHashedPassword);
        }
    }

    private function getRepository(): RepositoryInterface
    {
        /** @phpstan-ignore-next-line */
        return $this->orm->getRepository($this->class);
    }

    private function getUserPkValue(UserInterface $user): string|int
    {
        // @toto find new method for detection if PK value
        return method_exists($user, 'getId') ? $user->getId() : throw new AbstractException('Can not get PK value');
    }
}
