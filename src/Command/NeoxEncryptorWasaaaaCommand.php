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
    public  helperCommand $helperCommand;

    public function __construct(helperCommand $helperCommand)
    {
        $this->helperCommand = $helperCommand;
        parent::__construct();
        
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io                         = new SymfonyStyle($input, $output);
        $entity[]                   = "ALL";
        
        // finding all entities with properties to encrypt or decrypt
        $entitiesWithProperties     = $this->helperCommand->getList()->entitiesWithProperties;
        
        // foreach entity add it to list, to trait later
        // give back list of entities with properties to user "as status"
        foreach ($entitiesWithProperties as $entityData) {
            $io->title(sprintf('[Find in] Entity : %s ', $entityData['entity']));
            $io->text($entityData["properties"]);
            $entity[] = $entityData['entity'];
        }
        
        // if $entitiesWithProperties is empty, stop the script
        if ($entitiesWithProperties) {
            $io->newLine();
            // ask which action user wants to doo ?
            $question           = new ChoiceQuestion('Select action : default [Finish]', ["Finish", "Encrypt", "Decrypt"], "Finish");
            $action             = $this->getHelper('question')->ask($input, $output, $question);
            
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
            $question           = new ChoiceQuestion("Please choose the ENTITY you want to {$action}:", $entity);
            $question->setErrorMessage('ENTITY : %s does not exist.');
            $processing             = $this->getHelper('question')->ask($input, $output, $question);
            
            // loop through one/all entities to encrypt/decrypt
            if ($processing === "ALL") {
                foreach ($entitiesWithProperties as $entity) {
                    $this->helperCommand->neoxDoctrineSecure->setEntityConvert($entity['entity'], $action);
                    
                    $io->success("Processing [$action] Entity : {$entity['entity']}");
                }
            }else{
                $io->success("Processing [$action] Entity : {$entity['entity']}");
            }
            
            
            $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
        }else{
            $io->success('No entity found to encrypt or decrypt, nothing has been changed.');
        }
        
        return Command::SUCCESS;
    }
}
