<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Contrôleur de déconnexion de l'espace
 */
class DeconnexionController extends ControllerBase
{
	/**
	 * Initialisation du contrôleur
	 * Chargement du fichier de langue correspondant au contrôleur
	 * Récupération du titre dans le fichie de langue du contrôleur
	 * Appel de l'initialisation du contrôleur parent
	 */
    public function initialize()
    {
		//Chargement du fichier de langue correspondant au contrôleur
        $this->loadCustomTrans('deconnexion');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
		//Appel de l'initialisation du contrôleur parent
        parent::initialize();
    }
	
	/**
	 * Action principale
	 * Destruction de la session
	 * Recréation de la session
	 * Redirection vers formulaire de login
	 */
    public function indexAction()
    {
		$this->log('');
		
		//Destruction de la session
		$this->session->destroy();
		
		//Recréation de la session
		$this->session->start();
		
		//Redirection vers formulaire de login
		$this->session->set('info', $this->t['deconnecte']);
		$this->response->redirect('login');
		return true;
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
            $this->loadCustomTrans('deconnexion');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'deconnexion', 'action' => 'index'));
        }
    }
}
