<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setReference('26-TMB-001');
        $product->setName('Table Monolithe Bourbonnais');
        $product->setShortDescription('Plateau chêne pleine masse 75mm à bords adoucis. Piétement sculptural en double poutre HEA d\'acier.');
        $product->setDescription('Quam ob rem circumspecta cautela observatum est deinceps et cum edita montium petere coeperint grassatores, loci iniquitati milites cedunt. ubi autem in planitie potuerint reperiri, quod contingit adsidue, nec exsertare lacertos nec crispare permissi tela, quae vehunt bina vel terna, pecudum ritu inertium trucidantur.');
        $product->setPhotoPath('products/bourbonnais.png');
        $product->setPrice(3850);
        $product->setStockQuantity(1);
        $manager->persist($product);

        $product = new Product();
        $product->setReference('14-BAV-002');
        $product->setName('Bureau Architecte Vulcain');
        $product->setShortDescription('Bureau en chêne massif avec piétement en acier. Plateau avec finition huilée.');
        $product->setDescription('Hacque adfabilitate confisus cum eadem postridie feceris, ut incognitus haerebis et repentinus, hortatore illo hesterno clientes numerando, qui sis vel unde venias diutius ambigente agnitus vero tandem et adscitus in amicitiam si te salutandi adsiduitati dederis triennio indiscretus et per tot dierum defueris tempus, reverteris ad paria perferenda, nec ubi esses interrogatus et quo tandem miser discesseris, aetatem omnem frustra in stipite conteres summittendo.');
        $product->setPhotoPath('products/vulcain.png');
        $product->setPrice(2490);
        $product->setStockQuantity(4);
        $manager->persist($product);

        $product = new Product();
        $product->setReference('07-BT5-003');
        $product->setName('Bibliothèque Tenseur 5');
        $product->setShortDescription('Bibliothèque modulable en chêne massif avec structure en acier.');
        $product->setDescription('Oportunum est, ut arbitror, explanare nunc causam, quae ad exitium praecipitem Aginatium inpulit iam inde a priscis maioribus nobilem, ut locuta est pertinacior fama. nec enim super hoc ulla documentorum rata est fides.');
        $product->setPhotoPath('products/tenseur5.png');
        $product->setPrice(3200);
        $product->setStockQuantity(2);
        $manager->persist($product);

        $product = new Product();
        $product->setReference('19-BBE-004');
        $product->setName('Banc Brute Enclume 220');
        $product->setShortDescription('Banc en chêne massif avec piétement en acier. Longueur 220 cm.');
        $product->setDescription('Quibus occurrere bene pertinax miles explicatis ordinibus parans hastisque feriens scuta qui habitus iram pugnantium concitat et dolorem proximos iam gestu terrebat sed eum in certamen alacriter consurgentem revocavere ductores rati intempestivum anceps subire certamen cum haut longe muri distarent, quorum tutela securitas poterat in solido locari cunctorum.');
        $product->setPhotoPath('products/enclume.png');
        $product->setPrice(1450);
        $product->setStockQuantity(3);
        $manager->persist($product);

        $product = new Product();
        $product->setReference('22-CTC-005');
        $product->setName('Console Traversière Cantilever');
        $product->setShortDescription('Console en chêne massif avec structure en acier cantilever.');
        $product->setDescription('Et quia Montius inter dilancinantium manus spiritum efflaturus Epigonum et Eusebium nec professionem nec dignitatem ostendens aliquotiens increpabat, qui sint hi magna quaerebatur industria, et nequid intepesceret, Epigonus e Lycia philosophus ducitur et Eusebius ab Emissa Pittacas cognomento, concitatus orator, cum quaestor non hos sed tribunos fabricarum insimulasset promittentes armorum si novas res agitari conperissent.');
        $product->setPhotoPath('products/cantilever.png');
        $product->setPrice(1190);
        $product->setStockQuantity(8);
        $manager->persist($product);

        $product = new Product();
        $product->setReference('11-DTR-006');
        $product->setName('Duo Tabourets Rivet 75');
        $product->setShortDescription('Rondin de chêne tourné manuellement laissant voir les cernes de croissance. Base trépied rivetée à chaud .');
        $product->setDescription('Quam ob rem ut ii qui superiores sunt submittere se debent in amicitia, sic quodam modo inferiores extollere. Sunt enim quidam qui molestas amicitias faciunt, cum ipsi se contemni putant; quod non fere contingit nisi iis qui etiam contemnendos se arbitrantur; qui hac opinione non modo verbis sed etiam opere levandi sunt.');
        $product->setPhotoPath('products/rivet75.png');
        $product->setPrice(890);
        $product->setStockQuantity(12);
        $manager->persist($product);

        $manager->flush();
    }
}
