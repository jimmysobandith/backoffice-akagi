<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Contrôleur d'édition des évènements
 */
use Phalcon\Mvc\Controller,
	Phalcon\Mvc\View;

class EvenementsController extends ControllerBase
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
        $this->loadCustomTrans('evenements');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Envoi de l'information de menu
		$this->view->setVar('amevenements', 'active');
		$this->view->setVar('aevenements', 'active');
		
		//Header + Footer
		$this->view->setTemplateBefore('header');
		$this->view->setTemplateAfter('footer');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Évènements');
		$breadcrumbs[2] = array('', 'Évènements');
		
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
		
		//Total des evenements en base de données
		$totals = $this->modelsManager->createBuilder()
			->columns(array('count(e.id) as nb'))
			->addFrom('Evenements', 'e')
			->getQuery()
			->execute();
		$total = $totals[0];
		$total = $total->nb;
		
		$this->view->setVar("total", $total);
		
		//Informations en session
		$sess_start = $this->session->get('sess_start_evenements');
		$sess_order = $this->session->get('sess_order_evenements');
		$sess_search = $this->session->get('sess_search_evenements');
		$sess_length = $this->session->get('sess_length_evenements');
		
		$this->view->setVar("sess_start_evenements", $sess_start);
		$this->view->setVar("sess_order_evenements", $sess_order);
		$this->view->setVar("sess_search_evenements", $sess_search);
		$this->view->setVar("sess_length_evenements", $sess_length);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "evenements.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action de listing des evenements en ajax
	 * Données reçues des dataTables
	 * Gestion de l'ordre d'affichage
	 * Récupération des evenements selon les paramètres
	 * Total des evenements
	 * Total des filtrés
	 * Génération du json
	 * Affichage du json
	 */
    public function listingAction()
    {
		$this->view->setRenderLevel(View::LEVEL_NO_RENDER);
		$t = $this->t;
		
		//Données reçues des dataTables
		$draw = $this->request->getPost('draw', 'int');
		$start = $this->request->getPost('start', 'int');
		$length = $this->request->getPost('length', 'int');
		$search = $this->request->getPost('search', 'string'); //Array
		$order = $this->request->getPost('order'); //Array
		$columns = $this->request->getPost('columns'); //Array
		
		$search_value = $search['value'];
		$search_regex = $search['regex'];
		
		//Mise en session des données de filtre
		$this->session->set('sess_search_evenements', $search_value);
		$this->session->set('sess_order_evenements', $order);
		$this->session->set('sess_length_evenements', $length);
		$this->session->set('sess_start_evenements', $start);
		
		
		//Gestion de l'ordre d'affichage
		$orderby = 'e.id asc';
		if (count($order) > 0)
		{
			switch ($order[0]['column'])
			{
				case '1': $orderby = 'e.nom'; break; //Nom
				case '2': $orderby = 'e.ville'; break; //Nom
				case '3': $orderby = 'e.type'; break; //Nom
				default: $orderby = 'e.id'; break; //ID par défaut
			}
			
			switch ($order[0]['dir'])
			{
				case 'desc': $orderby .= ' desc'; break; //DESC
				case 'asc': $orderby .= ' asc'; break; //ASC
				default: $orderby .= ' desc'; break; //ASC
			}
		}
		
		//Récupération des evenements selon les paramètres
		if (trim($search_value) != '')
		{
			$evenements = $this->modelsManager->createBuilder()
				->columns(array('e.id', 'e.nom', 'e.ville', 'e.type'))
				->addFrom('Evenements', 'e')
				->where('e.id like "%'.$search_value.'%" or e.nom like "%'.$search_value.'%" or e.ville like "%'.$search_value.'%" or e.type like "%'.$search_value.'%"')
				->limit($length, $start)
				->getQuery()
				->execute();
		}
		else
		{
			$evenements = $this->modelsManager->createBuilder()
				->columns(array('e.id', 'e.nom', 'e.ville', 'e.type'))
				->addFrom('Evenements', 'e')
				->limit($length, $start)
				->getQuery()
				->execute();
		}
		
		//Total des realisations
		$totals = $this->modelsManager->createBuilder()
			->columns(array('count(e.id) as nb'))
			->addFrom('Evenements', 'e')
			->getQuery()
			->execute();
		$total = $totals[0];
		$total = $total->nb;
		
		//Total des filtrés
		if (trim($search_value) != '')
		{
			$filtres = $this->modelsManager->createBuilder()
				->columns(array('e.id'))
				->addFrom('Evenements', 'e')
				->where('e.id like "%'.$search_value.'%" or e.nom like "%'.$search_value.'%" or e.ville like "%'.$search_value.'%" or e.type like "%'.$search_value.'%"')
				->orderBy($orderby)
				->getQuery()
				->execute();
			$filtre = count($filtres);
		}
		else
		{
			$filtres = $this->modelsManager->createBuilder()
				->columns(array('e.id'))
				->addFrom('Evenements', 'e')
				->orderBy($orderby)
				->getQuery()
				->execute();
			$filtre = count($filtres);
		}
		
		//Génération du json
		$json = '{
"draw": '.$draw.',
"recordsTotal": '.$total.',
"recordsFiltered": '.$filtre.',
"data": [';
		
		$i=0;
		foreach ($evenements as $evenement)
		{
			//Construction finale du json : ID, titre, paragraphe
			if ($i > 0)
			{
				$json .= ',
			';
			}
			$json .= '["'.$evenement->id.'",
			"'.$evenement->nom.'",
			"'.$evenement->ville.'",
			"'.$evenement->type.'",
			"<a href=\'evenements/detail/'.$evenement->id.'\'>'.$t['modifier'].'</a>",
			"<a href=\'javascript: supprimer('.$evenement->id.');\'>'.$t['supprimer'].'</a>"]';
			$i++;
		}
		
		$json .= ']}';
		
		//Affichage du json
		echo $json;
		
		return true;
	}
	
	/**
	 * Action de suppression
	 * Récupération de la réalisation demandé
	 * Suppression des historiques
	 * Suppression des logs
	 * Suppression de la réalisation
	 * Redirection vers l'index
	 */
    public function supprimerAction($id_evenement)
    {
		$this->log('');
		
		//Récupération du type de realisation demandé
		$evenements = $this->modelsManager->createBuilder()
			->from(array('Evenements'))
			->where('Evenements.id = "'.$id_evenement.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($evenements) == 0)
		{
			$this->session->set('err', $this->t['err_supprimer']);
			$this->response->redirect('evenements');
			return false;
		}
		else
		{
			$evenement = $evenements[0];
		}
		
		//Suppression de la realisation
		$evenement->delete();
		
		//Redirection vers l'index
		$this->session->set('succ', $this->t['succ_supprimer']);
		$this->response->redirect('evenements');
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
		$breadcrumbs[1] = array('', 'Évènements');
		$breadcrumbs[2] = array('Évènements', 'Évènements');
		$breadcrumbs[3] = array('', 'Ajouter un évènements');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);

		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "evenements.css");
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
		$nom = $this->request->getPost('nom', 'string');
		$ville = $this->request->getPost('ville', 'string');
		$type = $this->request->getPost('type', 'string');
		$date = $this->request->getPost('date', 'string');
		
		
		//Vérification validité des données
		if (empty($nom))
		{
			$this->session->set('err', $this->t['err_nom']);
			$this->response->redirect('evenements/ajouter');
			return false;
		}
		
		//Ajout en base de données
		$evenement = new Evenements();
		
		$evenement->nom = $nom;
		$evenement->ville = $ville;
		$evenement->type = $type;
		$evenement->date = "";
		$saved = $evenement->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['err_save']);
			$this->response->redirect('evenements/ajouter');
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
	 * Récupération de l'évènement demandé
	 * Gestion du fil d'ariane
	 * Envoi des variables vers la vue
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function detailAction($id_evenement)
    {
		$this->log('');
		
		//Récupération de la réalisation demandé
		$evenements = $this->modelsManager->createBuilder()
			->from(array('Evenements'))
			->where('Evenements.id = "'.$id_evenement.'"')
			->limit(1)
			->getQuery()
			->execute();
		if (count($evenements) == 0)
		{
			$this->session->set('err', $this->t['err_detail']);
			$this->response->redirect('evenements');
			return false;
		}
		else
		{
			$evenement = $evenements[0];
		}
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('index', 'Accueil');
		$breadcrumbs[1] = array('', 'Évènements');
		$breadcrumbs[2] = array('Évènements', 'Évènements');
		$breadcrumbs[3] = array('', 'Évènements : '.$evenement->titre);
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Envoi des variables vers la vue
		$this->view->setVar('evenement', $evenement);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "evenements.css");
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
	public function enregistrerAction($id_evenement)
	{
		$this->log('');
		
		//Réception des valeurs
		$retour = $this->request->getPost('retour', 'int');
		
		//Réception des valeurs
		$nom = $this->request->getPost('nom', 'string');
		$ville = $this->request->getPost('ville', 'string');
		$type = $this->request->getPost('type', 'string');
		$date = $this->request->getPost('date', 'string');
		
		//Vérification validité des données
		if (empty($nom))
		{
			$this->session->set('err', $this->t['en_err_nom']);
			$this->response->redirect('evenements/detail/'.$id_evenement);
			return false;
		}
		
		//Récupération de l'utilisateur
		$evenements = $this->modelsManager->createBuilder()
			->from(array('Evenements'))
			->where('Evenements.id = "'.$id_evenement.'"')
			->limit(1)
			->getQuery()
			->execute();
			
		if (count($evenements) == 0)
		{
			$this->session->set('err', $this->t['err_enregistrer']);
			$this->response->redirect('evenements/detail/'.$id_evenement);
			return false;
		}
		else
		{
			$evenement = $evenements[0];
		}
		
		//Mise à jour en base de données
		$evenement->nom = $nom;
		$evenement->ville = $ville;
		$evenement->type = $type;
		$evenement->date = "";
		$saved = $evenement->save();
		
		if ($saved == false)
		{
			$this->session->set('err', $this->t['en_err_save']);
			$this->response->redirect('evenements/detail/'.$id_evenement);
			return false;
		}
		
		//Vidage session et redirection
		if (! empty($retour))
		{
			//Appui sur le bouton retour
			$this->session->set('succ', $this->t['succ_en'].$evenement->id);
			$this->session->set('scrollto', $evenement->id);
			$this->response->redirect('evenements');
			return true;
		}
		else
		{
			//Retour par défaut vers la fiche détail
			$this->session->set('succ', $this->t['succ_en'].$evenement->id);
			$this->response->redirect('evenements/detail/'.$id_evenement);
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
            $this->loadCustomTrans('evenements');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'evenements', 'action' => 'index'));
        }
    }
}
