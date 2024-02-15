<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Events;
    
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Contracts\EventDispatcher\Event;
    use ReflectionClass;
    
    class NeoxEncryptorEvent extends Event
    {
        public CONST EVENT_ENCRYPTOR_KEY = "neox.encryptor.key";
   
        private ?string $msg = null;
        private ?string $key = null;
        
        public function __construct( readonly mixed $EncryptorClass, readonly mixed $entity)
        {
      
        }
        
        public function getEncryptorClass(): mixed
        {
            return $this->EncryptorClass;
        }
        
        public function getMsg(): ?string
        {
            return $this->msg;
        }
        
        public function setMsg(string $msg): void
        {
            $this->msg = $msg;
        }
        
        public function getKey(): ?string
        {
            return $this->key;
        }
        
        public function setKey(string $key): void
        {
            $this->key = $key;
        }
    }