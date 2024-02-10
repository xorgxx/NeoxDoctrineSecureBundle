<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command\Helper;
    
    use Doctrine\Persistence\ManagerRegistry;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineSecure;
    
    class helperCommand
    {
        public array $entitiesWithProperties = [];
        
        public function __construct(readonly ManagerRegistry $doctrine, readonly NeoxDoctrineSecure $neoxDoctrineSecure )
        {
        }
        
        public function getList(): helperCommand
        {
            $metadata   = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
            
            foreach ($metadata as $classMetadata) {
                $entityName     = $classMetadata->getName();
                $properties     = $classMetadata->getFieldNames();
                $propertiesList = [];
                
                foreach ($properties as $property) {
                    foreach ($classMetadata->getReflectionProperty($property)->getAttributes() as $attribute) {
                        if ($attribute->getName() === neoxEncryptor::class) {
                            $fieldMapping       = $classMetadata->getFieldMapping($property);
                            $type               = $fieldMapping['type'] ?? null;
                            $length             = isset($fieldMapping['length']) ? ' - ' . $fieldMapping['length'] : '';
                            $propertiesList[]   = $type ? sprintf('    - Property : %s ( %s%s ) ', $property, $type, $length) : $property;
                            break;
                        }
                    }
                }
                
                if (!empty($propertiesList)) {
                    $this->entitiesWithProperties[] = [
                        'entity'        => $entityName,
                        'properties'    => $propertiesList
                    ];
                }
            }
            return $this;
        }
    }