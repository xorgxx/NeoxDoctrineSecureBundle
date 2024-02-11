<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use ReflectionClass;
    use ReflectionException;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    final class NeoxDoctrineSecure
    {
        public int $counterSecure = 0;
        private ReflectionClass $reflectionClass;
        
        /**
         * @var array|mixed
         */
        public mixed $cachedEntity;
        
        /**
         * @var array|mixed
         */
        public mixed $ParametersBag;
        
        private Dsn $dsn;
        
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager)
        {
        }

        public function setEntityConvert($entity, $action){
            if ($Entity    = $this->entityManager->getRepository($entity)->findall()) {
                foreach ($Entity as $item) {
                    if ($action === "Decrypt") {
                        $this->decryptFields($item);
                    }else{
                        $this->encryptFields($item);
                    }
                    $this->entityManager->persist($item);
                }
                $this->entityManager->flush();
            }
            return "ok";
        }
        /**
         * @throws ReflectionException
         */
        public function encryptFields($entity): void
        {
            if ($this->getEncryptorClass($entity)) {
                $this->processFields($entity, function ($value, $type){
                    return ($this->createServiceClassInstance())->encrypt($value, $type);
                });
            }
        }
        
        /**
         * @throws ReflectionException
         */
        public function decryptFields($entity): void
        {
            if ($this->getEncryptorClass($entity)) {
                $this->processFields($entity, function ($value, $type) {
                    return ($this->createServiceClassInstance())->decrypt($value, $type);
                });
            }
        }
        
        private function processFields($entity, callable $processor): void
        {
            
            foreach ($this->reflectionClass->getProperties() as $property) {
                $encryptAttribute = $property->getAttributes(neoxEncryptor::class)[0] ?? null;
                if ($encryptAttribute !== null) {
                    // notify that we are "processing"
                    $this->counterSecure++;
                    // get the type to later use to process the value by Type
                    $type                                   = $property->getType()->getName();
                    
                    // get the value item
                    $value                                  = $property->getValue($entity);
                    
                    // process the value Encrypt/decrypt
                    $processedValue                         = $processor($value, $type);
                    
                    // cache the value entity for later in process eventDoctine to retrieve
//                    $this->cachedEntity[$entity::class]     = $value;
                    
                    // set the value - Encrypted/decrypted
                    $property->setValue($entity, $processedValue);
                }
            }
        }
        
        /**
         * This will check if the class has the neoxEncryptor attribute and process if it has Only !
         *
         * @param $entity
         *
         * @return ReflectionClass |null
         * @throws ReflectionException
         */
        private function getEncryptorClass($entity): ?ReflectionClass
        {
            $this->reflectionClass      = $this->getReflectionClass($entity);
            $file                       = $this->reflectionClass->getFileName();
            $pattern                    = "/use NeoxDoctrineSecure\\\\NeoxDoctrineSecureBundle\\\\Attribute\\\\neoxEncryptor;/";
            
            return (!is_file($file) || !preg_match($pattern, file_get_contents($file))) ? null : $this->reflectionClass;
        }
        
        public function getReflectionClass($entity): ReflectionClass
        {
            return new ReflectionClass($entity);
        }
        private function prepareClassName(): string
        {
            $this->getDsn($this->parameterBag->get("neox_doctrine_secure.neox_dsn"));
            $service    = $this->dsn->getScheme() ?? "Defuse";
            $namespace  = "NeoxDoctrineSecure\\NeoxDoctrineSecureBundle\\Pattern\\Services\\";
            return $namespace . ucfirst($service) . "Service";
        }
        
        /**
         * @throws \ReflectionException
         */
        private function createServiceClassInstance()
        {
            $className = $this->prepareClassName();
            if (class_exists($className)) {
                return (new \ReflectionClass($className))->newInstance($this->dsn);
            }
        }
        
        private function getDsn(string $dsn): void
        {
            $this->dsn = new Dsn($dsn);
            // Build the query string
            $query          = http_build_query($this->dsn->getOptions(), '', '&', PHP_QUERY_RFC3986);
            $this->dsn->setUri("https://" . $this->dsn->getHost() . $this->dsn->getPath() . '?access_key=' .$this->dsn->getUser() .'&' . $query);
        }
    }