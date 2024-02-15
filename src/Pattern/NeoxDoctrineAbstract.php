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
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events\NeoxEncryptorEvent;
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    
    
    ABSTRACT class NeoxDoctrineAbstract implements neoxDoctrineInterface
    {
        protected ReflectionClass $reflectionClass;
        protected mixed $EncryptorClass;
        public int      $counterSecure   = 0;
        public mixed    $cachedEntity;
        protected       Dsn $dsn;
        protected string $indice;
        protected Data|null $DataEncrypt;
        protected mised $clone;
        
        
        public function __construct(
            readonly ParameterBagInterface $parameterBag,
            readonly EntityManagerInterface $entityManager,
            readonly EventDispatcherInterface $EventDispatcherInterface
        
        )
        {
        }
        
        public function getReflectionClass($entity): ?ReflectionClass
        {
            if (!($entity instanceof Data)) {
                $this->reflectionClass  = new ReflectionClass($entity);
                if($this->checkClassHaveEncryptor()) {
                    
                    $listener   = new NeoxEncryptorEvent($this->reflectionClass, $entity);
                    $this->EventDispatcherInterface->dispatch($listener, NeoxEncryptorEvent::EVENT_ENCRYPTOR_KEY);
                    
                    $msg        = $listener->getMsg() ?? $this->reflectionClass->getName() . "::" . $entity->getId();
                    $key        = $listener->getKey() ?? $this->reflectionClass->getShortName();
                    
                    $this->getEncryptionKey($msg, $key);
                    
                    $this->dataCrypt();
                    return $this->reflectionClass;
                }
            }
            return null;
        }
        
        protected function checkClassHaveEncryptor()
        {
            // Finding the presence of the class or namespace to check in the source code
            return strpos(file_get_contents($this->reflectionClass->getFileName()), neoxEncryptor::class) !== false;
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
            $encryptionKey  = KeyFactory::deriveEncryptionKey($key, $this->getSalt());
            $this->indice   = Util::keyed_hash($msg, $encryptionKey,16);
        }
        
        public function setEncryptorClass(mixed $EncryptorClass): self
        {
            $this->EncryptorClass = $EncryptorClass;
            return $this;
        }
        
        protected function getSalt(){
            return $this->parameterBag->get('neox_doctrine_secure.neox_salt');
        }
        
        protected function callBackType(string $type)
        {
            $msg = [
                // Used to represent strings. It can be configured with a maximum length.
                "String"   => "<enc>",
                // Used to represent integers.
                "Integer"  => 7,
                // Used to represent integers smaller than Integer.
                "SmallInt" => 77,
                // Used to represent integers larger than Integer.
                "BigInt"   => 777,
                // Used to represent Boolean values (TRUE or FALSE).
                "Boolean"  => true,
                // Used to represent a date and time.
                "DateTime" => "2000-02-02 02:02:02",
                // Used to represent a date only.
                "Date"     => "2000-02-02",
                // Used to represent one hour only.
                "Time"     => "02:02:02",
                // Used to represent decimal floating point numbers.
                "Float"    => 777.0,
                // Used to represent decimal numbers.
                "Decimal"  => 777.7,
                // Used to represent an array.
                "Array"    => ["007"=>"007"],
                // Used to represent an object.
                "Object"   =>  [
                    "Decimal" => 777.7,
                    "Array"   => ["007" => "007"],
                ],
                
            ];
            return $msg[$type];
        }
    }