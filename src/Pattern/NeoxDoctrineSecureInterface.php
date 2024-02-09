<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    interface NeoxDoctrineSecureInterface
    {
        public function encrypt($value, $type): string;

        public function decrypt($value, $type): string;
    }