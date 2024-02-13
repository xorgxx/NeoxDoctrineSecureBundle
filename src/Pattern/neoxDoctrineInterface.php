<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    interface neoxDoctrineInterface
    {
        /**
         * A description of the entire PHP function.
         *
         * @param datatype $entity description
         * @param datatype $action description
         * @throws Some_Exception_Class description of exception
         * @return string
         */
        public function setEntityConvert($entity, $action): string;
        
        public function encryptFields($entity): self;
        
        public function decryptFields($entity, $mode = true): self;
        
        public function processFields($entity, callable $processor, bool $mode = true): void;
        
        public function getReflectionClass($entity): ?\ReflectionClass;
        
        public function setEncryptorClass(mixed $EncryptorClass): self;
    }