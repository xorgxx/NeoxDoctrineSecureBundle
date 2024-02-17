<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Pattern;
    
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\EventSubscriber\NeoxDoctrineSecureSubscriber;
    use Doctrine\Persistence\ManagerRegistry;
    use Doctrine\ORM\Events;
    
    class NeoxDoctrineTools
    {
        public function __construct(readonly ManagerRegistry $doctrine){
            
        }
        
        public  function EventListener(bool $etat = false) {
            $eventManager       = $this->doctrine->getManager()->getEventManager();
            if ($etat) {
                $eventManager->addEventListener([Events::postLoad], NeoxDoctrineSecureSubscriber::class);
            }else{
                $eventManager->removeEventListener([Events::postLoad], NeoxDoctrineSecureSubscriber::class);
            }
        }
    }