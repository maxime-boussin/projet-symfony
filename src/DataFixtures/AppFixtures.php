<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Excursion;
use App\Entity\Message;
use App\Entity\Place;
use App\Entity\Site;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Migrations\Version\Factory;
use Faker;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder, $em;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->encoder = $encoder;
        $this->em = $em;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        $userAdmin = new User();
        $userAdmin->setFirstName("Admin_prénom");
        $userAdmin->setLastName("Admin_nom");
        $userAdmin->setPhone("0606060606");
        $userAdmin->setEmail("admin@sortir.com");
        $userAdmin->setPassword($this->encoder->encodePassword($userAdmin, "test12"));

        $siteAdmin = new Site();
        $siteAdmin->setName("Paris");

        $userAdmin->setSite($siteAdmin);
        $userAdmin->setNickname("Admin");
        $userAdmin->setRoles(["ROLE_USER","ROLE_ADMIN"]);
        $userAdmin->setActive(1);
        $manager->persist($siteAdmin);
        $manager->persist($userAdmin);

        $sites = [];
        $sites[0] = new Site();
        $sites[0]->setName("Rennes");
        $sites[1] = new Site();
        $sites[1]->setName("Nantes");
        $sites[2] = new Site();
        $sites[2]->setName("Lyon");
        $manager->persist($sites[0]);
        $manager->persist($sites[1]);
        $manager->persist($sites[2]);

        $cities = [];

        for ($i = 0; $i < 20; $i++) {
            $cities[$i] = new City();
            $cities[$i]->setName($faker->city);
            $cities[$i]->setPostCode($this->strNoAccentNoSpace($faker->postcode));
            $manager->persist($cities[$i]);
        }

        $allUsers = [];

        for ($i = 0; $i < 50; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setPhone($faker->phoneNumber);
            $user->setEmail(strtolower($this->strNoAccentNoSpace($user->getFirstName()[0])).".".strtolower($this->strNoAccentNoSpace($user->getLastName())).rand(1,95)."@sortir.com");
            $user->setPassword($this->encoder->encodePassword($user, "test12"));
            $user->setSite($sites[rand(0,2)]);
            $user->setNickname($this->strForPseudo($faker->userName));
            $user->setRoles(["ROLE_USER"]);
            $user->setActive(1);
            $manager->persist($user);
            $allUsers[$i] = $user;
        }

        for ($i = 0; $i < 200; $i++){
            $excursion = new Excursion();
            $organizer = $allUsers[rand(0,49)];
            $excursion->setDate($faker->dateTimeBetween('-366 days', '+365 days'));
            $excursion->setLimitDate($faker->dateTimeBetween('-365 days', $excursion->getDate()));
            try {
                $excursion->setDuration(new \DateInterval('P0Y' . rand(0, 4) . 'DT' . rand(0, 23) . 'H' . rand(1, 59) . 'M'));
            } catch (\Exception $e) {
                $excursion->setDuration(new \DateInterval('P0Y0DT1H30M'));
            }
            $excursion->setName($faker->realText($faker->numberBetween(20,60)));
            $excursion->setDescription($faker->realText($faker->numberBetween(200,500)));
            $excursion->setVisibility(1);
            $excursion->setParticipantLimit(rand(1,15));
            for ($j = 0; $j < rand(0,$excursion->getParticipantLimit()); $j++){
                $userParticipant = $allUsers[rand(0,49)];
                if ($userParticipant != $organizer){
                    $excursion->addParticipant($userParticipant);
                }
            }
            $excursion->setSite($organizer->getSite());
            $excursion->setOrganizer($organizer);

            $place = new Place();
            $place->setAddress($faker->buildingNumber." ".$faker->streetName);
            $place->setCity($cities[rand(0,18)]);
            $place->setLatitude($faker->latitude);
            $place->setLongitude($faker->longitude);

            $excursion->setPlace($place);
            $excursion->setState(rand(0,4));

            $manager->persist($place);
            $manager->persist($excursion);
        }
        $manager->flush();
    }

    function strNoAccentNoSpace($str)
    {
        $url = $str;
        $url = preg_replace('# #', '', $url);
        $url = preg_replace('#Ç#', 'C', $url);
        $url = preg_replace('#ç#', 'c', $url);
        $url = preg_replace('#[èéêë]#', 'e', $url);
        $url = preg_replace('#[ÈÉÊË]#', 'E', $url);
        $url = preg_replace('#[àáâãäå]#', 'a', $url);
        $url = preg_replace('#[@ÀÁÂÃÄÅ]#', 'A', $url);
        $url = preg_replace('#[ìíîï]#', 'i', $url);
        $url = preg_replace('#[ÌÍÎÏ]#', 'I', $url);
        $url = preg_replace('#[ðòóôõö]#', 'o', $url);
        $url = preg_replace('#[ÒÓÔÕÖ]#', 'O', $url);
        $url = preg_replace('#[ùúûü]#', 'u', $url);
        $url = preg_replace('#[ÙÚÛÜ]#', 'U', $url);
        $url = preg_replace('#[ýÿ]#', 'y', $url);
        $url = preg_replace('#Ý#', 'Y', $url);

        return ($url);
    }

    function strForPseudo ($str){
        $str = preg_replace('#\.#', '-', $str);
        return $str;
    }
}
