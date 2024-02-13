<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use Doctrine\ORM\EntityManagerInterface;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Entity\NeoxEncryptor as Data;
    use ParagonIE\Halite\KeyFactory;
    use ParagonIE\Halite\Symmetric\EncryptionKey;
    use ParagonIE\Halite\Util;
    use ParagonIE\HiddenString\HiddenString;
    use ReflectionClass;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    
    ABSTRACT class NeoxDoctrineAbstract implements neoxDoctrineInterface
    {
        protected ReflectionClass $reflectionClass;
        protected mixed $EncryptorClass;
        
        public int      $counterSecure   = 0;
        public mixed    $cachedEntity;
        protected       Dsn $dsn;
        protected string $indice;
        private const SALT = "**@#$#*#&%&@$&^@";
        protected Data|null $DataEncrypt;
        protected mised $clone;
        
        
        public function __construct(readonly ParameterBagInterface $parameterBag, readonly EntityManagerInterface $entityManager)
        {
        }
        
        public function getReflectionClass($entity): ?ReflectionClass
        {
            if ($entity instanceof Data) {
                return null;
            }
            
            $this->reflectionClass  = new ReflectionClass($entity);
            if($this->checkClassHaveEncryptor()) {
                $this->getEncryptionKey($this->reflectionClass->getName() . "::" . $entity->getId(), $this->reflectionClass->getShortName());
                $this->dataCrypt();
                return $this->reflectionClass;
            }
            return null;
        }
        
        protected function checkClassHaveEncryptor()
        {
            $classSource = file_get_contents($this->reflectionClass->getFileName());
            // Finding the presence of the class or namespace to check in the source code
            return strpos($classSource, neoxEncryptor::class) !== false ? true : false;
        }
        
        public function dataCrypt()
        {
            // ff5d400f96d533dfda3018dc7dce45f5
            $data                   = $this->indice;
            $this->DataEncrypt      = $this->entityManager->getRepository(Data::class)->findOneBy(['data' => $data]);
        }
        
        private function getEncryptionKey(string $msg = "", string $key = ""): void
        {
            $key            = new HiddenString($key);
            $encryptionKey  = KeyFactory::deriveEncryptionKey($key, self::SALT);
            $this->indice   = Util::keyed_hash($msg, $encryptionKey,16);
        }
        
        public function setEncryptorClass(mixed $EncryptorClass): self
        {
            $this->EncryptorClass = $EncryptorClass;
            return $this;
        }
    }