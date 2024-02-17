<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command\Helper;
    
    use Doctrine\Persistence\ManagerRegistry;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineFactory;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineSecure;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\EventSubscriber\NeoxDoctrineSecureSubscriber;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern\NeoxDoctrineAbstract;
    class helperCommand
    {
        public array $entitiesWithProperties = [];
        
        public function __construct(readonly ManagerRegistry $doctrine, readonly NeoxDoctrineFactory $neoxDoctrineFactory)
        {
        
        }
        
        public function checkSchemaSupported(string $schema) : bool
        {
            return $this->neoxDoctrineFactory->getDsnSchema() === $schema;
        }
        public function getList(): helperCommand
        {
            $metadata = $this->doctrine->getManager()->getMetadataFactory()->getAllMetadata();
            
            foreach ($metadata as $classMetadata) {
                $entityName     = $classMetadata->getName();
                $properties     = $classMetadata->getFieldNames();
                $propertiesList = [];
                
                
                foreach ($properties as $property) {
                    foreach ($classMetadata->getReflectionProperty($property)->getAttributes() as $attribute) {
                        if ($attribute->getName() === neoxEncryptor::class) {
                            
                            $eventManager       = $this->doctrine->getManager()->getEventManager();
                            $eventManager->removeEventListener([\Doctrine\ORM\Events::postLoad], NeoxDoctrineSecureSubscriber::class);
                            $eventManager->addEventListener([\Doctrine\ORM\Events::postLoad], NeoxDoctrineSecureSubscriber::class);
                            $entity             = $this->doctrine->getManager()->getRepository($entityName)->findOneBy([]);
                     
      
                            $fieldMapping       = $classMetadata->getFieldMapping($property);
                            $type               = $fieldMapping['type'] ?? null;
                            $length             = isset($fieldMapping['length']) ? ' - ' . $fieldMapping['length'] : '';
                            $value              = $classMetadata->getFieldValue($entity, $property);
                            $value              = NeoxDoctrineAbstract::callBackType("",$value) ? "External" : "standalone";
                            $propertiesList[]   = $type ? sprintf('   %s - Property : %s ( %s%s ) ', $value, $property, $type, $length) : $property;
                            
                            break;
                        }
                    }
                }
                
                if (!empty($propertiesList)) {
                    $this->entitiesWithProperties[] = [
                        'entity'        => $entityName,
                        'properties'    => $propertiesList,
                        'encryptor'     => $value
                    ];
                }
                
                
            }
            return $this;
        }
        
    }