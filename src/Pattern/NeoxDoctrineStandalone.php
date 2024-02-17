<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    final class NeoxDoctrineStandalone extends NeoxDoctrineAbstract
    {
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager)
        {
        }
        
        // Function use for command line
        public function setEntityConvert($entity, $action): string
        {
            if ($Entity = $this->entityManager->getRepository($entity)->findall()) {
                $this->cachedEntity = [];
                foreach ($Entity as $item) {
                    if ($action === "Decrypt") {
                        $this->setByPassListenerEvent(false);
                        $this->decryptFields($item, false);
                    } else {
                        $this->setByPassListenerEvent(true);
                        $this->encryptFields($item);
                    }
                    $this->entityManager->persist($item);
                }
                $this->setByPassListenerEvent(true);
                $this->entityManager->flush();
            }
            return "ok";
        }
        
        public function encryptFields($entity, $mode = true): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->processFields($entity, fn($value, $type) => $this->EncryptorClass->encrypt($value, $type), $mode);
            }
            return $this;
        }
        
        public function decryptFields($entity, $mode = true): self
        {
            if ($reflection = $this->getReflectionClass($entity)) {
                $this->processFields($entity, fn($value, $type) => $this->EncryptorClass->decrypt($value, $type), $mode);
            }
            return $this;
        }

        public function processFields($entity, callable $processor, bool $mode = true): void
        {
            foreach ($this->reflectionClass->getProperties() as $property) {
                // filter on "neoxEncryptor" attribute
                // https://www.php.net/manual/fr/reflectionclass.getattributes.php
                $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                if ($encryptAttribute !== null) {
                    // notify that we are "processing"
                    $this->counterSecure++;
                    // get the type to later use to process the value by Type
                    $type = $property->getType()->getName();

                    // get the value item
                    $value = $property->getValue($entity);

                    // process the value Encrypt/decrypt
                    $processedValue = $processor($value, $type);

                    // cache the value entity for later in process eventDoctine to retrieve
//                    if ($mode) {
                        $this->cachedEntity[$entity::class] = $value;
//                    };
//                    $this->cachedEntity[$entity::class]     = $value;

                    // set the value - Encrypted/decrypted
                    $property->setValue($entity, $processedValue);
                }
            }
        }

    }