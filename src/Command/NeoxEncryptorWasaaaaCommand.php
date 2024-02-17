<?php
    
    namespace NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command;
    
    use Doctrine\Persistence\ManagerRegistry;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Attribute\neoxEncryptor;
    use NeoxDoctrineSecure\NeoxDoctrineSecureBundle\Command\Helper\helperCommand;
    use Symfony\Component\Console\Attribute\AsCommand;
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    use Symfony\Component\Console\Question\ChoiceQuestion;
    use Symfony\Component\Console\Style\SymfonyStyle;
    
    #[AsCommand(
        name: 'neox:encryptor:wasaaaa',
        description: 'Add a short description for your command',
    )]
    class NeoxEncryptorWasaaaaCommand extends Command
    {
        // XDEBUG_TRIGGER=1 php bin/console neox:encryptor:wasaaaa
        public helperCommand $helperCommand;
        
        public function __construct(helperCommand $helperCommand)
        {
            $this->helperCommand = $helperCommand;
            parent::__construct();
            
        }
        
        protected function configure(): void
        {
            $this
                ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
                ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
        }
        
        protected function execute(InputInterface $input, OutputInterface $output): int
        {
            $io = new SymfonyStyle($input, $output);
            $entity[] = "ALL";

//        $this->helperCommand->neoxDoctrineFactory->buildEncryptor()->setEntityConvert(null, null);
//        $io->success("Processing [$action] Entity : {$entity['entity']}");
            
            // check schema supported dont need any more
//            if (!$this->helperCommand->checkSchemaSupported("standalone")) {
//                $io->error('Schema not supported');
//                $io->info('External schema is not supported yet! Please, use standalone schema instead (.env | NEOX_ENCRY_DSN=standalone://redis)!');
//                return Command::FAILURE;
//            };
            
            // finding all entities with properties to encrypt or decrypt
            $entitiesWithProperties = $this->helperCommand->getList()->entitiesWithProperties;
            
            // foreach entity add it to list, to trait later
            // give back list of entities with properties to user "as status"
            $encryptor = "";
            foreach ($entitiesWithProperties as $entityData) {
                $io->title(sprintf('[Find in] Entity : %s ', $entityData['entity']));
                $io->text($entityData["properties"]);
                $entity[] = $entityData['entity'];
                $encryptor = $encryptor === "" ? $entityData["encryptor"] : $encryptor;
            }
            
            $io->warning("If your data is encrypted on External schema. If you want to pass to standalone crypt we recommand to uncrypte (as External) it first and then change schema to standalone and encrypt.");
            $io->warning("If your data is encrypted on Standalone schema. If you want to pass to External crypt we recommand to uncrypte (as Standalone) it first and then change schema to external and encrypt.");
            
            
            // if $entitiesWithProperties is empty, stop the script
            if ($entitiesWithProperties) {
                $io->newLine();
                // ask which action user wants to doo ?
                $question = new ChoiceQuestion('Select action : default [Finish]', ["Finish",
                    "Encrypt",
                    "Decrypt"], "Finish");
                $action = $this->getHelper('question')->ask($input, $output, $question);
                
                switch ($action) {
                    case "Finish":
                        $io->success('Nothing has been changed.');
                        return Command::SUCCESS;
                    case "Encrypt":
                    case "Decrypt":
                        break;
                    default:
                        return Command::SUCCESS;
                }
                
                // Ask user which entity should be moved.
                $question = new ChoiceQuestion("Please choose the ENTITY you want to {$action}:", $entity);
                $question->setErrorMessage('ENTITY : %s does not exist.');
                $processing = $this->getHelper('question')->ask($input, $output, $question);
                
                // loop through one/all entities to encrypt/decrypt
                foreach ($entitiesWithProperties as $entity) {
                    if ($processing === "ALL") {
                        $this->helperCommand->neoxDoctrineFactory->buildEncryptor()->setEntityConvert($entity['entity'], $action);
                        $io->success("Processing [$action] Entity : {$entity['entity']}");
                        
                    } else {
                        $this->helperCommand->neoxDoctrineFactory->buildEncryptor()->setEntityConvert($entity['entity'], $action);
                        $io->success("Processing [$action] Entity : {$entity['entity']}");
                        break;
                    }
                }
                
                $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
            } else {
                $io->success('No entity found to encrypt or decrypt, nothing has been changed.');
            }
            
            return Command::SUCCESS;
        }
    }
