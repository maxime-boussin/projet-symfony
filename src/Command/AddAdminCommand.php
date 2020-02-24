<?php
namespace App\Command;

use App\Entity\Excursion;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddAdminCommand extends Command
{
    protected static $defaultName = 'app:admin:add';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Upgrade a regular user to administrator.')
            ->addArgument('email', InputArgument::REQUIRED, 'Email of user to upgrade.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $input->getArgument('email')]);
        if($user instanceof User){
            $roles = $user->getRoles();
            array_push($roles, 'ROLE_ADMIN');
            $user->setRoles($roles);
            $this->em->flush($user);
            $output->writeln(sprintf('%s successfully added to admin.', $user->getNickname() ));
        }
        else{
            $output->writeln('Error, unknown user.');
        }
        return 0;
    }
}