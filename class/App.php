<?php
/*
 * This file is part of Phantastic.
 *
 * Phantastic is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Phantastic is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Phantastic.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Malenki\Phantastic;

use Malenki\Opt\Options as Options;
use Malenki\Opt\Arg as Arg;


/**
 * Le moteur de l’application.
 *
 * Mise en place des paramètre, détection des options choisies par 
 * l’utilisateur, et lancement du processus. 
 * 
 * @copyright 2012 Michel Petit
 * @author Michel Petit <petit.michel@gmail.com> 
 */
class App
{
    /**
     * Mise en place des options et de leur message d’aide. 
     * 
     * @access public
     * @return void
     */
    public function setOpt()
    {

        Options::add(
            Arg::createValue('source')
            ->setShort('s:')
            ->setLong('source:')
            ->setHelp('Le dossier contenant les fichiers à traiter.')
            ->setVarHelp('DIR')
        );

        Options::add(
            Arg::createValue('destination')
            ->setShort('d:')
            ->setLong('destination:')
            ->setHelp('Le dossier dans lequel seront créés les fichiers.')
            ->setVarHelp('DIR')
        );

        Options::add(
            Arg::createValue('baseurl')
            ->setShort('b:')
            ->setLong('baseurl:')
            ->setHelp('URL de base utilisé pour le site généré. Cette valeur n’est pas utilisée si l’option « server » est choisie.')
            ->setVarHelp('BASE_URL')
        );

        Options::add(
            Arg::createValue('config')
            ->setShort('c::')
            ->setLong('config::')
            ->setHelp('Fichier de configuration contenant différentes valeurs sous forme d’un fichier YAML. Si FICHIER n’est pas spécifié, alors un fichier « config.yaml » sera lu par défaut, mais s’il n’existe pas, déclenchera une erreur.')
            ->setVarHelp('FICHIER')
        );

        Options::add(
            Arg::createSwitch('minimize')
            ->setLong('minimize')
            ->setHelp('Réduit la taille des fichiers générés.')
        );

        Options::add(
            Arg::createValue('timezone')
            ->setLong('timezone:')
            //TODO: Être plus bavard là…
            ->setHelp('Fuseau horaire TZ à utiliser pour les dates, comme par exemple « Europe/Paris ». La valeur utilisée par défaut est « UTC ».')
            ->setVarHelp('TZ')
        );
        
        Options::add(
            Arg::createValue('server')
            ->setLong('server::')
            ->setHelp('Fait un rendu et lance un serveur web de test à l’adresse ADR:PORT. Si l’adresse n’est pas précisée, alors « localhost:8080 » sera prise. Si l’option « baseurl » est précisée, elle sera ignorée.')
            ->setVarHelp('ADR:PORT')
        );


        Options::getInstance()->setHelp('Affiche ce message d’aide.');
        Options::getInstance()->setVersion('Affiche la version de Phantastic.');
    }

    /**
     * Récupère les options passées au programme et met en place la 
     * configuration. 
     * 
     * @access public
     * @return void
     */
    public function getOpt()
    {
        // OK, on interpète ce qu’on a en ligne de commande et on détermine quoi faire…
        Options::getInstance()->parse();

        if(Options::getInstance()->has('version'))
        {
            printf("\nPHANTASTIC Version 0.1\n\n");
            exit();
        }

        if(Options::getInstance()->has('help'))
        {
            Options::getInstance()->displayHelp();
        }

        if(Options::getInstance()->has('config'))
        {
            $str_config_file = 'config.yaml';

            if(Options::getInstance()->get('config'))
            {
                $str_config_file = Options::getInstance()->get('config');
            }


            if(is_readable($str_config_file))
            {
                Config::getInstanceWithConfigFile($str_config_file);
            }

        }
        else
        {
            if(Options::getInstance()->has('timezone'))
            {
                Config::getInstance()->setTimezone($opt->get('timezone'));
            }
            
            
            if(Options::getInstance()->has('server'))
            {
                Config::getInstance()->setServer($opt->get('server'));
            }
        }
    }




    /**
     * Lance le générateur, le serveur… Bref, le cœur du programme ! 
     * 
     * @access public
     * @return void
     */
    public function run()
    {
        date_default_timezone_set(Config::getInstance()->getTimezone());

        $g = new Generator();
        $g->getData();
        $g->render();
        $g->renderTagPages();

        //Ce qui suit n’a aucun intérêt car les catégories font parties intégrantes des 
        //fichiers.
        //TODO: Euh, en fait si :) Il faut mettre des pages au niveau des nœuds non finaux.
        //$g->renderCategoryPages();

        //debug, test…
        //var_dump(History::getLast());
        //var_dump(Category::getTree(0));


        if(Config::getInstance()->getServer())
        {
            if(Server::canRun())
            {
                printf("Serveur de test lancé à l’adresse http://%s. Pour quitter, pressez « Contrôle-C »\n", Config::getInstance()->getServer());
                $s = new Server();
                $s->setHost(Config::getInstance()->getServer());
                $s->run();
            }
            else
            {
                //TODO: Utiliser la futur classe de Log pour ce message
                printf("Impossible de lancer un serveur sur cette 
                    configuration. Mettez à jour PHP ou installez Python. 
                    Sinon désactivez l’option de lancement d’un serveur dans 
                    l’appel à Phantastic ou dans votre fichier de 
                    configuration\n");
            }
        }
    }
}

