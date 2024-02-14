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
            $encryptor = $this->setEncryptorClassInstance();
            if ($this->dsn->getScheme() === "standalone") {
                return $this->neoxDoctrineStandalone->setEncryptorClass($encryptor);
            } elseif ($this->dsn->getScheme() === "external") {
                return $this->neoxDoctrineExtern->setEncryptorClass($encryptor);
            } else {
                throw new \RuntimeException(sprintf("Schema '%s' not found! standalone or external in .env file| NEOX_ENCRY_DSN", $this->dsn->getScheme()));
            }
        }
        
        private function getEncryptorClass(): string
        {
//            $this->getDsn($this->parameterBag->get("neox_doctrine_secure.neox_dsn"));
            $service            = $this->parameterBag->get("neox_doctrine_secure.neox_encryptor") ?? "Halite";
            return "NeoxDoctrineSecure\\NeoxDoctrineSecureBundle\\Pattern\\Services\\" . ucfirst($service) . "Service";
            
            // build path to services encrypt for windows or linux
            // NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\Services
//            $parts              = ["NeoxDoctrineSecure", "NeoxDoctrineSecureBundle", "Pattern", "Services"];
//            $namespace          = implode(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR;
//
//            return $namespace . ucfirst($service) . "Service";
        }
        
        public function getDsnSchema(): string
        {
            return $this->dsn->getScheme();
        }
        
            private function setEncryptorClassInstance() : mixed
        {
            $className = $this->getEncryptorClass();
            if (!class_exists($className)) {
                throw new \RuntimeException(sprintf("Class '%s' not found", $className));
            }
            return (new $className($this->parameterBag));
        }
        
        private function getDsn(string $dsn): void
        {
            if (empty($dsn)) {
                throw new \InvalidArgumentException("Dsn not found. Did you forget to set NEOX_ENCRY_DSN in the .env file?");
            }
            
            $this->dsn = new Dsn($dsn);
            // Build the query string
            $query = http_build_query($this->dsn->getOptions(), '', '&', PHP_QUERY_RFC3986);
            $this->dsn->setUri("https://{$this->dsn->getHost()}{$this->dsn->getPath()}?access_key={$this->dsn->getUser()}&{$query}");
        }
        
    }