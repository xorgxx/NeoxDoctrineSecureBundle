<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Entity\NeoxEncryptor as Data;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events\NeoxEncryptorEvent;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    
    final class NeoxDoctrineExtern extends NeoxDoctrineAbstract
    {
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
            $entities = $this->entityManager->getRepository($entity)->findAll();
            if (empty($entities)) {
                return "ok";
            }
            
            $this->cachedEntity = [];
            foreach ($entities as $item) {
                if ($action === "Decrypt") {
                    $this->decryptFields($item, false);
                } else {
                    $this->encryptFields($item);
                }
                $this->entityManager->persist($item);
            }
            $this->entityManager->flush();
            
            return "ok";
        }
        
        public function encryptFields($entity): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->processFields($entity, fn($value, $type) => $this->EncryptorClass->encrypt($value, $type), true);
            }
            return $this;
        }
        
        public function decryptFields($entity, $mode = false): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
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
                    $type = $property->getType()->getName();
                    // get data
                    $propertyName       = $property->getName();
                    // get the value item
                    $value              = $property->getValue($entity);
                    $neoxEncryptValue   = json_decode($this->DataEncrypt->getContent())->$propertyName;
                    
                    if ($mode) {
                        $processedValue                 = $processor($value, $type);
                        $items[$property->getName()]    = $processedValue;
                        $processedValue                 = $this->callBackType($type);
                    }else{
                        // process the value Encrypt/decrypt
                        $processedValue = $processor($neoxEncryptValue, $type);
                    }
                    
                    // set the value - Encrypted/decrypted
                    $property->setValue($entity, $processedValue);
                    
                    // cache the value entity for later in process eventDoctine to retrieve
                    $this->cachedEntity[$entity::class] = $value;
                }
            }
            
//            if ($items) {
//                // {"content":"dede fredf","description":"03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8 03a61a73aae2d803a61a73aae2d8 03a61a73aae2d8"}
//                $this->cachedEntity[Data::class] = [$this->indice, json_encode($items)];
//            }
//
            if ($mode) {
                $itemsContent = json_encode($items);
                if ($this->DataEncrypt) {
                    $this->DataEncrypt->setContent($itemsContent);
                } else {
                    $this->DataEncrypt = (new Data())
                        ->setContent($itemsContent)
                        ->getId($this->indice);
                }
                
                $this->cachedEntity = [];
                $this->entityManager->persist($this->DataEncrypt);
                $this->entityManager->flush();
            }
        }
    }