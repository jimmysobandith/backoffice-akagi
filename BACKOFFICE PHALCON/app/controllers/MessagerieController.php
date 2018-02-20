<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Contrôleur d'édition des realisations
 */
use Phalcon\Mvc\Controller,
	Phalcon\Mvc\View;

class MessagerieController extends ControllerBase
{
	/**
	 * Pré-initialisation du contrôleur
	 * Vérification utilisateur identifié
	 */
	public function beforeExecuteRoute($dispatcher)
    {
		//Vérification utilisateur identifié
		$this->check_admin();
    }
	
	/**
	 * Initialisation du contrôleur
	 * Chargement du fichier de langue correspondant au contrôleur
	 * Récupération du titre dans le fichie de langue du contrôleur
	 * Envoi de l'informatin de menu
	 * Header + Footer
	 * Gestion du fil d'ariane
	 * Appel de l'initialisation du contrôleur parent
	 */
    public function initialize()
    {
		//Chargement du fichier de langue correspondant au contrôleur
        $this->loadCustomTrans('messagerie');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Envoi de l'information de menu
		$this->view->setVar('amrealisations', 'active');
		$this->view->setVar('arealisations', 'active');
		
		//Header + Footer
		$this->view->setTemplateBefore('header');
		$this->view->setTemplateAfter('footer');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Réalisations');
		$breadcrumbs[2] = array('', 'Réalisations');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Appel de l'initialisation du contrôleur parent
        parent::initialize();
    }

	/**
	 * Action principale
	 * ID utilisateur
	 * Réception du dernier utilisateur édité
	 * Total des utilisateurs en base de données
	 * Informations en session
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function indexAction()
    {
		$this->log('');
		
		//ID utilisateur
		$u_id = $this->session->get('u_id');
		
		//Réception du dernier utilisateur édité
		$scrollto = $this->session->get('scrollto');
		$this->session->remove('scrollto');
		$scrollto = (empty($scrollto))?0:$scrollto;
		$this->view->setVar('scrollto', $scrollto);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "realisations.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action de listing des realisations en ajax
	 * Données reçues des dataTables
	 * Gestion de l'ordre d'affichage
	 * Récupération des realisations selon les paramètres
	 * Total des realisations
	 * Total des filtrés
	 * Génération du json
	 * Affichage du json
	 */
    public function listingAction()
    {
		
	}
	
	/**
	 * Action de suppression
	 * Récupération de la réalisation demandé
	 * Suppression des historiques
	 * Suppression des logs
	 * Suppression de la réalisation
	 * Redirection vers l'index
	 */
    public function supprimerAction($id_realisation)
    {
		$this->log('');
		
		//Récupération du type de realisation demandé
		$realisations = $this->modelsManager->createBuilder()
			->from(array('Realisations'))
			->where('Realisations.id = "'.$id_realisation.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($realisations) == 0)
		{
			$this->session->set('err', $this->t['err_supprimer']);
			$this->response->redirect('realisations');
			return false;
		}
		else
		{
			$realisation = $realisations[0];
		}
		
		//Suppression de la realisation
		$realisation->delete();
		
		//Redirection vers l'index
		$this->session->set('succ', $this->t['succ_supprimer']);
		$this->response->redirect('realisations');
		return true;
	}
	
	/**
	 * Action d'affichage du formulaire d'ajout
	 * Gestion du fil d'ariane
	 * Vérification si valeur en session et envoi vers la vue
	 * Envoi des informations CSS et langue vers la vue
	 */
	public function ajouterAction()
	{
		$this->log('');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Réalisation');
		$breadcrumbs[2] = array('realisations', 'realisations');
		$breadcrumbs[3] = array('', 'Ajouter un realisations');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Vérification si valeur en session et envoi vers la vue
		$titre = $this->session->get('titre');
		$this->view->setVar('titre', $titre);
		$paragraphe = $this->session->get('paragraphe');
		$this->view->setVar('paragraphe', $paragraphe);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "realisations.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action d'ajout
	 * Réception des valeurs
	 * Mise en session des valeurs
	 * Vérification validité des données
	 * Création de mot de passe
	 * Ajout en base de données
	 * Vidage session et redirection
	 */
	public function nouveauAction()
	{
		$this->log('');
		
		//Réception des valeurs
		$titre = $this->request->getPost('titre', 'string');
		$paragraphe = $this->request->getPost('paragraphe', 'string');
		
		//Mise en session
		$this->session->set('titre', $titre);
		$this->session->set('paragraphe', $paragraphe);
		
		//Vérification validité des données
		if (empty($titre))
		{
			$this->session->set('err', $this->t['err_nom']);
			$this->response->redirect('realisations/ajouter');
			return false;
		}
		
		//Si pièce Jointe
		if ($this->request->hasFiles())
        {
			echo 'gregre';
            $i=1;
            foreach ($this->request->getUploadedFiles() as $file)
            {
                $key = $file->getKey();
                $type = $file->getType();
               
                //Image
                if (($key == 'logo') && (! empty($type)))
                {
                    if ( ($type != 'image/jpg') )
                    {
                       $this->session->set('err', $this->t['err_ext']);
                       $this->response->redirect('realisation/ajouter/');
                       return false;
                    }
                   
                    $baseLocation = '/home/acticam/www/img/upload/';
                    $baseLocation2 = '/home/acticam/www/img/realisation/';
                   
				    $url = $nom;
					$url = preg_replace('#Ç#', 'C', $url);
					$url = preg_replace('#ç#', 'c', $url);
					$url = preg_replace('#è|é|ê|ë#', 'e', $url);
					$url = preg_replace('#È|É|Ê|Ë#', 'E', $url);
					$url = preg_replace('#à|á|â|ã|ä|å#', 'a', $url);
					$url = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url);
					$url = preg_replace('#ì|í|î|ï#', 'i', $url);
					$url = preg_replace('#Ì|Í|Î|Ï#', 'I', $url);
					$url = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url);
					$url = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url);
					$url = preg_replace('#ù|ú|û|ü#', 'u', $url);
					$url = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url);
					$url = preg_replace('#ý|ÿ#', 'y', $url);
					$url = preg_replace('#Ý#', 'Y', $url);
					
				    $url2 = $prenom;
					$url2 = preg_replace('#Ç#', 'C', $url2);
					$url2 = preg_replace('#ç#', 'c', $url2);
					$url2 = preg_replace('#è|é|ê|ë#', 'e', $url2);
					$url2 = preg_replace('#È|É|Ê|Ë#', 'E', $url2);
					$url2 = preg_replace('#à|á|â|ã|ä|å#', 'a', $url2);
					$url2 = preg_replace('#@|À|Á|Â|Ã|Ä|Å#', 'A', $url2);
					$url2 = preg_replace('#ì|í|î|ï#', 'i', $url2);
					$url2 = preg_replace('#Ì|Í|Î|Ï#', 'I', $url2);
					$url2 = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $url2);
					$url2 = preg_replace('#Ò|Ó|Ô|Õ|Ö#', 'O', $url2);
					$url2 = preg_replace('#ù|ú|û|ü#', 'u', $url2);
					$url2 = preg_replace('#Ù|Ú|Û|Ü#', 'U', $url2);
					$url2 = preg_replace('#ý|ÿ#', 'y', $url2);
					$url2 = preg_replace('#Ý#', 'Y', $url2);
     
					$url = strtolower($url);
					$url2 = strtolower($url2);
					
                    $filename = $url.'.jpg';
                    $filenameT = $url2.'-'.$url.'.jpg';
                   
                    $file->moveTo($baseLocation.$filename);
                   
                   
                    $dest = imagecreatetruecolor($width = $size[0] , $height = $size[1]) or die ("Erreur");
                   
                    imagecopyresampled($dest , $source, 0,0, 0,0, $width = $size[0], $height = $size[1], $width = $size[0], $height = $size[1]);
                   
                    imagedestroy($source);
                   
                    imagepng($dest , $baseLocation2.$filenameT);
                   
                    //Deleting temp
                    unlink($baseLocation.$filename);
                   
                }		
            }
			
        }
		
		//Ajout en base de données
		$realisation = new Realisations();
		
		$realisation->titre = $titre;
		$realisation->paragraphe = $paragraphe;
		$realisation->image = $filenameT;
		$saved = $realisation->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['err_save']);
			$this->response->redirect('realisations/ajouter');
			return false;
		}
		
		//Vidage session et redirection
		$this->session->remove('titre');
		$this->session->remove('paragraphe');
		$this->session->set('succ', $this->t['succ_aj'].$realisation->id);
		$this->response->redirect('realisations/detail/'.$realisation->id);
		return true;
	}
	
	/**
	 * Action de détail d'une realisation
	 * Récupération de la realisation demandé
	 * Gestion du fil d'ariane
	 * Envoi des variables vers la vue
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function detailAction($id_realisation)
    {
		$this->log('');
		
		//Récupération de la réalisation demandé
		$realisations = $this->modelsManager->createBuilder()
			->from(array('Realisations'))
			->where('Realisations.id = "'.$id_realisation.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($realisations) == 0)
		{
			$this->session->set('err', $this->t['err_detail']);
			$this->response->redirect('realisation');
			return false;
		}
		else
		{
			$realisation = $realisations[0];
		}
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Réalisation');
		$breadcrumbs[2] = array('realisations', 'realisations');
		$breadcrumbs[3] = array('', 'Réalisation : '.$realisation->titre);
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Envoi des variables vers la vue
		$this->view->setVar('realisation', $realisation);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "realisations.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action d'enregistrement
	 * Réception des valeurs
	 * Vérification validité des données
	 * Récupération de l'utilisateur
	 * Mise à jour en base de données
	 * Vidage session et redirection
	 */
	public function enregistrerAction($id_utilisateur)
	{
		$this->log('');
		
		//Réception des valeurs
		$retour = $this->request->getPost('retour', 'int');
		
		$nom = $this->request->getPost('nom', 'string');
		$prenom = $this->request->getPost('prenom', 'string');
		$email = $this->request->getPost('email', 'email');
		$fonction = $this->request->getPost('fonction', 'string');
		$password = $this->request->getPost('password', 'string');
		$confirmation_password = $this->request->getPost('confirmation_password', 'string');
		
		//Vérification validité des données
		if (empty($nom))
		{
			$this->session->set('err', $this->t['en_err_nom']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		if (empty($email))
		{
			$this->session->set('err', $this->t['en_err_email']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		
		//Récupération de l'utilisateur
		$utilisateurs = $this->modelsManager->createBuilder()
			->from(array('Utilisateurs'))
			->where('Utilisateurs.id = "'.$id_utilisateur.'"')
			->limit(1)
			->getQuery()
			->execute();
			
		if (count($utilisateurs) == 0)
		{
			$this->session->set('err', $this->t['err_enregistrer']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		else
		{
			$utilisateur = $utilisateurs[0];
		}
		
		//Création mot de passe si champs non vide
		if(!empty($password))
		{
			if($password == $confirmation_password)
			{
				$timeTarget = 0.2; 
 
				$cost = 9;
				do {
					$cost++;
					$start = microtime(true);
					password_hash($password, PASSWORD_BCRYPT, ["cost" => $cost]);
					$end = microtime(true);
				} while (($end - $start) < $timeTarget);
				
				$hash = password_hash($password,PASSWORD_BCRYPT,['cost' => $cost]);
				
				$utilisateur->pass = $hash;
			}
			else
			{
				$this->session->set('err', $this->t['err_password']);
				$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
				return false;
			}
		}
		
		//Mise à jour en base de données
		$utilisateur->nom = $nom;
		$utilisateur->prenom = $prenom;
		$utilisateur->email = $email;
		$utilisateur->fonction = $fonction;
		
		$saved = $utilisateur->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['en_err_save']);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return false;
		}
		
		//Vidage session et redirection
		if (! empty($retour))
		{
			//Appui sur le bouton retour
			$this->session->set('succ', $this->t['succ_en'].$utilisateur->id);
			$this->session->set('scrollto', $utilisateur->id);
			$this->response->redirect('utilisateurs');
			return true;
		}
		else
		{
			//Retour par défaut vers la fiche détail
			$this->session->set('succ', $this->t['succ_en'].$utilisateur->id);
			$this->response->redirect('utilisateurs/detail/'.$id_utilisateur);
			return true;
		}
	}
	
	/**
	 * Changement de langue
	 * Rechargement des traductions si nécessaire
	 * Retour à la dernière page
	 */
    public function setLanguageAction($language='')
    {
        //Rechargement des traductions si nécessaire
        if ($language == 'fr') {
            $this->session->set('language', $language);
            $this->loadMainTrans();
            $this->loadCustomTrans('realisations');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'realisations', 'action' => 'index'));
        }
    }
}
