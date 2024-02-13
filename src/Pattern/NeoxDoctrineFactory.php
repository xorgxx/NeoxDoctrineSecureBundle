<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    class NeoxDoctrineFactory
    {
        private Dsn $dsn;
        
        public function __construct(
            readonly ParameterBagInterface $parameterBag,
            readonly EntityManagerInterface $entityManager,
            readonly NeoxDoctrineStandalone $neoxDoctrineStandalone,
            readonly NeoxDoctrineExtern $neoxDoctrineExtern,
        )
        {
            $this->getDsn($this->parameterBag->get("neox_doctrine_secure.neox_dsn"));
        }
        
        public function buildEncryptor(): mixed
        {
            switch ($schema = $this->dsn->getScheme()){
                // Building SIMPLE encryptor and manage build-in in same Entity and only type string-255 or text
                case "standalone":
                    return $this->neoxDoctrineStandalone->setEncryptorClass($this->setEncryptorClassInstance());
                    break;
                    
                // Building EXTERNAL encryptor and manage in separate Entity/Db (Redis, ...) all type end rend with Type-hinting
                case "external":
                    return $this->neoxDoctrineExtern->setEncryptorClass($this->setEncryptorClassInstance());
                    break;
                    
                default:
                    // this is boooooooowwww
                    throw new \RuntimeException(sprintf("Schema '%s' not found! standalone or external in .env file| NEOX_ENCRY_DSN", $schema));
            }
        }
        
        private function getEncryptorClass(): string
        {
//            $this->getDsn($this->parameterBag->get("neox_doctrine_secure.neox_dsn"));
            $service            = $this->parameterBag->get("neox_doctrine_secure.neox_encryptor") ?? "Halite";
     
            // build path to services encrypt for windows or linux
            // NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services
            $parts              = ["NeoxDoctrineSecure", "NeoxDoctrineSecureBundle", "Pattern", "Services"];
            $namespace          = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
            
            return $namespace . ucfirst($service) . "Service";
        }
        
        public function getDsnSchema(): string
        {
            return $this->dsn->getScheme();
        }
      
        private function setEncryptorClassInstance() : mixed
        {
            $className = $this->getEncryptorClass();
            if (class_exists($className)) {
                return (new \ReflectionClass($className))->newInstance($this->parameterBag);
            }
            
            throw new \RuntimeException(sprintf("Class '%s' not found", $className));
        }
        
        private function getDsn(string $dsn): void
        {
            if (empty($dsn)) {
                throw new \RuntimeException("Dsn not found. ded you forgot to set .env file| NEOX_ENCRY_DSN ?");
            }
            
            $this->dsn = new Dsn($dsn);
            // Build the query string
            $query          = http_build_query($this->dsn->getOptions(), '', '&', PHP_QUERY_RFC3986);
            $this->dsn->setUri("https://" . $this->dsn->getHost() . $this->dsn->getPath() . '?access_key=' .$this->dsn->getUser() .'&' . $query);
        }
        
    }