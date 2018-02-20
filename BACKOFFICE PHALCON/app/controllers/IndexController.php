<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Contrôleur de la page d'accueil
 */
class IndexController extends ControllerBase
{
	/**
	 * Initialisation du contrôleur
	 * Vérification utilisateur identifié
	 * Chargement du fichier de langue correspondant au contrôleur
	 * Récupération du titre dans le fichie de langue du contrôleur
	 * Envoi de l'informatin de menu
	 * Header + Footer
	 * Gestion du fil d'ariane
	 * Appel de l'initialisation du contrôleur parent
	 */
    public function initialize()
    {
		//Vérification utilisateur identifié
		$this->check_admin();
		
		//Chargement du fichier de langue correspondant au contrôleur
        $this->loadCustomTrans('index');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Envoi de l'information de menu
		$this->view->setVar('aindex', 'active');
		
		//Header + Footer
		$this->view->setTemplateBefore('header');
		$this->view->setTemplateAfter('footer');
		
		//Gestion du fil d'ariane
		$breadcrumbs[0] = array('', 'Accueil');
		
		$this->view->setVar('breadcrumbs', $breadcrumbs);
		
		//Appel de l'initialisation du contrôleur parent
        parent::initialize();
    }

	/**
	 * Action principale
	 * Envoi des informations CSS et langue vers la vue
	 */
    public function indexAction()
    {
		$this->log('');
		
		//Nombre d'utilisateurs
		$utilisateurs = $this->modelsManager->createBuilder()
			->columns(array('count(u.id) as nb'))
			->addFrom('Utilisateurs', 'u')
			->getQuery()
			->execute();
		$nb_utilisateurs = $utilisateurs[0];
		$nb_utilisateurs = $nb_utilisateurs->nb;
		
		$this->view->setVar('nb_utilisateurs', $nb_utilisateurs);
		$this->view->setVar('percent', 100);
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "index.css");
		$this->view->setVar("l", $this->session->get('language'));
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
            $this->loadCustomTrans('index');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'index', 'action' => 'index'));
        }
    }
}
