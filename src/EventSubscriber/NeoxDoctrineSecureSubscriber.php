<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\EventSubscriber;
    
    use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
    use Doctrine\ORM\Event\PostFlushEventArgs;
    use Doctrine\ORM\Event\PostLoadEventArgs;
    use Doctrine\ORM\Event\OnFlushEventArgs;
    use Doctrine\ORM\Events;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineFactory;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineStandalone;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineExtern;
    use ReflectionException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Entity\NeoxEncryptor;
    
    /**
     * Doctrine event subscriber which encrypt/decrypt entities
     */
    #[AsDoctrineListener(event: Events::postLoad, priority: 10, connection: 'default')]
    #[AsDoctrineListener(event: Events::onFlush, priority: 500, connection: 'default')]
    #[AsDoctrineListener(event: Events::postFlush, priority: 500, connection: 'default')]
    class NeoxDoctrineSecureSubscriber
    {
        public function __construct(readonly NeoxDoctrineFactory $neoxCryptorService, readonly ParameterBagInterface $parameterBag)
        {
        }
        
        /**
         * Listen a postLoad lifecycle event.
         * Decrypt entities property's values when loaded into the entity manger
         *
         * @param PostLoadEventArgs $args
         *
         * @throws ReflectionException
         */
        public function postLoad(PostLoadEventArgs $args): void
        {
            $entity         = $args->getObject();
            if ($Encryptor = $this->getEncryptor()) {
                $Encryptor->decryptFields($entity, false);
            }
            
        }
        
        /**
         * Listen to postFlush event
         * Decrypt entities after having been inserted (created) into the database
         *
         * @param PostFlushEventArgs $postFlushEventArgs
         *
         * @throws ReflectionException
         */
        public function postFlush(PostFlushEventArgs $postFlushEventArgs): void
        {
            // The first one is to add new entities source
            foreach ($postFlushEventArgs->getObjectManager()->getUnitOfWork()->getIdentityMap() as $entityMap) {
                /**
                 * On mode external to be created, dectection is $Encryptor->cachedEntity meaing that external as to be created
                 * if yes then encrypted fields need
                 */
                if ($Encryptor = $this->getEncryptor()) {
                    foreach ($entityMap as $entity) {
                        $reflector  = new \ReflectionClass($entity);
                        if ($Encryptor instanceof NeoxDoctrineExtern && isset($Encryptor->cachedEntity[$reflector->getName()])) {
                            $Encryptor->encryptFields($entity);
                        } else {
                            $this->getEncryptorFactory()?->buildEncryptor()->decryptFields($entity);
                        }
                    }  
                }
  
            }
        }
        
        /**
         * Listen to onflush event
         * Encrypt entities that are inserted into the database
         *
         * @param OnFlushEventArgs $onFlushEventArgs
         *
         * @throws ReflectionException
         */
        public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
        {
            $unitOfWork = $onFlushEventArgs->getObjectManager()->getUnitOfWork();
            
            foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
                $this->newItem($entity, $onFlushEventArgs, $unitOfWork);
                
            }
            foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
                $this->updateItem($unitOfWork);
            }
        }
        
        public static function getSubscribedEvents(): array
        {
            return [
                Events::postLoad,
                Events::onFlush,
                Events::postFlush,
            ];
        }
        
        /**
         * @param mixed            $entity
         * @param OnFlushEventArgs $onFlushEventArgs
         * @param                  $unitOfWork
         *
         * @return void
         * @throws ReflectionException
         */
        private function newItem(mixed $entity, OnFlushEventArgs $onFlushEventArgs, $unitOfWork): void
        {
            
            if ($Encryptor = $this->getEncryptor()) {
                $encryptCounterBefore = $Encryptor->counterSecure;
                $Encryptor->encryptFields($entity, false);
                
                if ($Encryptor->counterSecure > $encryptCounterBefore) {
                    $classMetadata = $onFlushEventArgs->getObjectManager()->getClassMetadata(get_class($entity));
                    $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity);
                }  
            }
      
            
        }
        
        /**
         * @param $unitOfWork
         *
         * @return void
         * @throws ReflectionException
         */
        
        private function updateItem($unitOfWork): void
        {
            if ($Encryptor = $this->getEncryptor()) {
                foreach ($unitOfWork->getIdentityMap() as $entityName => $entityArray) {
                    if (isset($Encryptor->cachedEntity[$entityName])) {
                        foreach ($entityArray as $entityId => $instance) {
                            $Encryptor->encryptFields($instance);
                        }
                    }
                }
                $Encryptor->cachedEntity = [];
            }

        }
        
        private function getEncryptorFactory(): ?NeoxDoctrineFactory
        {
            $off = $this->parameterBag->get("neox_doctrine_secure.neox_off");
            if ($off) {
                return null;
            }
            return $this->neoxCryptorService;
        }
        
        private function getEncryptor(): NeoxDoctrineExtern|NeoxDoctrineStandalone|null
        {
            $Encryptor  = $this->getEncryptorFactory()?->buildEncryptor();
            if ($Encryptor && !$Encryptor->byPassListenerEvent) {
                return $Encryptor;
            }
            return null;
        }
    }