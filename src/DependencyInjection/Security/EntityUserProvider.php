<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle\DependencyInjection\Security;

use Cycle\ORM\{EntityProxyInterface, ORMInterface, RepositoryInterface};
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

        return $this->removeEntityProxyWrapper($user);
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

        return $this->removeEntityProxyWrapper($refreshedUser);
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
        // @todo find new method for detection PK value
        return method_exists($user, 'getId') ? $user->getId() : throw new AbstractException('Can not get PK value');
    }

    /**
     * @todo this is method is a hack for removing EntityProxyInterface from user entity
     *       symfony use serialize/unserialize for authentication token
     *       and as result unserialize does not work correctly with EntityProxyInterface wrapper
     */
    private function removeEntityProxyWrapper(UserInterface $user): UserInterface
    {
        if (!$user instanceof EntityProxyInterface) {
            return $user;
        }

        /** @var \ReflectionClass */
        $parentReflection = (new \ReflectionClass($user))->getParentClass();
        $parentClassName = $parentReflection->getName();

        /** @var UserInterface */
        $originalUser = new $parentClassName();
        $originalReflection = new \ReflectionClass($originalUser);

        // set properties from parent to `$originalUser`
        foreach ($parentReflection->getProperties() as $parentProperty) {
            foreach ($originalReflection->getProperties() as $originalProperty) {
                if ($originalProperty->getName() !== $parentProperty->getName()) {
                    continue;
                }

                $originalProperty->setValue($originalUser, $parentProperty->getValue($user));

                break;
            }
        }

        return $originalUser;
    }
}
