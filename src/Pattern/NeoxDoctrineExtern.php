<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use App\Entity\Parameters;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Entity\NeoxEncryptor as Data;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events\NeoxEncryptorEvent;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use ReflectionClass;
    
    final class NeoxDoctrineExtern extends NeoxDoctrineAbstract
    {
        protected Dsn $dsn;
        
        public function __construct(
            ParameterBagInterface $parameterBag,
            EntityManagerInterface $entityManager,
            EventDispatcherInterface $EventDispatcherInterface
        )
        {
            Parent::__construct($parameterBag, $entityManager, $EventDispatcherInterface);
        }
        
        public function setEntityConvert($entity, $action): string
        {
            
            
            $reflector  = new \ReflectionClass($entity);
            $entities   = $this->entityManager->getRepository($entity)->findAll();
            if (empty($entities)) {
                return "ok";
            }
            
            foreach ($entities as $item) {
                
                if ($action === "Decrypt") {
                    $this->setByPassListenerEvent(false);
                    $this->getInfoEncryptor($item);
                    $this->entityManager->persist($item);
                    $this->entityManager->remove($this->DataEncrypt);
                    
                } else { 
                    $this->setByPassListenerEvent(true);
                    $this->encryptAll($item, true);
                   
                }
                $this->entityManager->persist($item);
            }
            $this->setByPassListenerEvent(true);
            $this->entityManager->flush();
            $this->cachedEntity = [];
            return "ok";
        }
        public function decryptAll($entity, $mode = false): void
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->getInfoEncryptor($entity);
                $this->entityManager->remove($this->DataEncrypt);
            }
        }
        
        public function encryptAll($entity, $mode = false): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->getInfoEncryptor($entity);
                $items = [];
                foreach ($this->reflectionClass->getProperties() as $property) {
                    // filter on "neoxEncryptor" attribute
                    // https://www.php.net/manual/fr/reflectionclass.getattributes.php
                    $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                    
                    if ($encryptAttribute !== null) {
                        // notify that we are "processing"
                        $this->counterSecure++;
                        // get the type to later use to process the value by Type
                        $type               = $property->getType()->getName();
                        // get data
                        $propertyName       = $property->getName();
                        // get the value item
                        $value              = $property->getValue($entity);
                        
                        $processedValue                 = $this->EncryptorClass->encrypt($value, $type);
                        $items[$property->getName()]    = $processedValue;
                        $processedValue                 = $this->callBackType($type);
        
                        
                        // set the value - Encrypted/decrypted
                        $property->setValue($entity, $processedValue);
             
                    }
                   
                }
                // set the value in Db encryptor
                $this->cachedEntity = [];
                $itemsContent = json_encode($items);
                if ($this->DataEncrypt ?? null) {
                    $this->DataEncrypt->setContent($itemsContent);
                } else {
                    $this->DataEncrypt = (new Data())
                        ->setContent($itemsContent)
                        ->setData($this->indice);
                }
                $this->entityManager->persist($this->DataEncrypt);
            }
            return $this;
        }
        
        
        public function encryptFields($entity, $mode = true): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->getInfoEncryptor($entity);
                $this->processFields($entity, fn($value, $type) => $this->EncryptorClass->encrypt($value, $type), $mode);
            }
            return $this;
        }
        
        public function decryptFields($entity, $mode = false): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->getInfoEncryptor($entity);
                $this->processFields($entity, fn($value, $type) => $this->EncryptorClass->decrypt($value, $type), $mode);
            }
            return $this;
        }
        
        public function processFields($entity, callable $processor, bool $mode = false): void
        {
            $items = [];
            foreach ($this->reflectionClass->getProperties() as $property) {
                // filter on "neoxEncryptor" attribute
                // https://www.php.net/manual/fr/reflectionclass.getattributes.php
                $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                
                if ($encryptAttribute !== null) {
                    // notify that we are "processing"
                    $this->counterSecure++;
                    // get the type to later use to process the value by Type
                    $type               = $property->getType()->getName();
                    // get data
                    $propertyName       = $property->getName();
                    // get the value item
                    $value              = $property->getValue($entity);
                    
                    if ( $entity->getId() && $i = $this->DataEncrypt?->getContent() ) {
                        $neoxEncryptValue   = json_decode($i)->$propertyName;
                        $processedValue     = $processor($neoxEncryptValue, $type);
                    }
                    // process the value Encrypt/decrypt
                    $processedValue     = $processedValue ?? $processor($value, $type);
                    if ($mode) {
                        $processedValue                 = $processor($value, $type);
                        $items[$property->getName()]    = $processedValue;
                        $processedValue                 = Parent::callBackType($type);
                    }
                    
                    // set the value - Encrypted/decrypted
                    $property->setValue($entity, $processedValue);
                    
                    // cache the value entity for later in process eventDoctine to retrieve
                    $this->cachedEntity[$entity::class] = $value;
                }
            }
            // set the value in Db encryptor
            $this->setDbNeoxEcryptor($mode, $items);
        }
        
        public function setDsn($dsn): self
        {
            $this->dsn = $dsn;
            return $this;
        }
        
        public function getDsn()
        {
            return $this->dsn;
        }
        
        private function getEncryptionKey(string $msg = "", string $key = ""): void
        {
//            $this->indice   = $msg;
            $key            = new HiddenString($key);
            $encryptionKey  = KeyFactory::deriveEncryptionKey($key, $this->getSalt());
            $this->indice   = Util::keyed_hash($msg, $encryptionKey,16);
        }
        
        /**
         * @param $entity
         *
         * @return void
         */
        private function getInfoEncryptor($entity): void
        {
            $listener = new NeoxEncryptorEvent($this->reflectionClass, $entity);
            $this->EventDispatcherInterface->dispatch($listener, NeoxEncryptorEvent::EVENT_ENCRYPTOR_KEY);
            $msg = $listener->getMsg() ?? $this->reflectionClass->getName() . "::" . $entity->getId();
            $key = $listener->getKey() ?? $this->reflectionClass->getShortName();
            $this->getEncryptionKey($msg, $key);
            $this->dataCrypt();
        }
        
        /**
         * @param bool  $mode
         * @param array $items
         *
         * @return void
         */
        private function setDbNeoxEcryptor(bool $mode, array $items): void
        {
            // set the new value in the entity (encrypted/decrypted) NeoxEncryptor or external
            if ($mode) {
                $itemsContent = json_encode($items);
                if ($this->DataEncrypt ?? null) {
                    $this->DataEncrypt->setContent($itemsContent);
                } else {
                    $this->DataEncrypt = (new Data())
                        ->setContent($itemsContent)
                        ->setData($this->indice);
                }
                
                $this->setByPassListenerEvent(false);
                $this->cachedEntity = [];
                $this->entityManager->persist($this->DataEncrypt);
                $this->entityManager->flush();
                $this->setByPassListenerEvent(true);
            }
        }
    }