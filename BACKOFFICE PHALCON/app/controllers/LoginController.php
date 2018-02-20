<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2015
 * Desc: Contrôleur de la page de login
 */
class LoginController extends ControllerBase
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
        $this->loadCustomTrans('login');
		
		//Récupération du titre dans le fichie de langue du contrôleur
		Phalcon\Tag::setTitle($this->t['title']);
		
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
		
		//Envoi des informations CSS et langue vers la vue
		$this->view->setVar("css", "login.css");
		$this->view->setVar("l", $this->session->get('language'));
	}
	
	/**
	 * Action d'identification sur l'espace
	 * Réception des valeurs
	 * Récupération de l'utilisateur en base de données
	 * Vérification du mot de passe
	 * Mise à jour de l'utilisateur
	 * Mise en session des informations de l'utilisateur
	 * Récupération du level
	 * Redirection vers le tableau de bord
	 */
    public function loginAction()
	{
		//Réception des valeurs
		$email = $this->request->getPost('email', 'email');
		$pass = $this->request->getPost('pass', 'string');
		
		//Récupération de l'utilisateur en base de données
		$utilisateurs = $this->modelsManager->createBuilder()
				->from(array('Utilisateurs'))
				->where('Utilisateurs.email = "'.$email.'"')
				->limit('1')
				->getQuery()
				->execute();
		if (count($utilisateurs) == 0)
		{
			//Utilisateur introuvable
			$this->session->set('err', $this->t['erreur_mail']);
			$this->response->redirect('login');
			return false;
		}
		else
		{
			//Utilisateur trouvé
			$utilisateur = $utilisateurs[0];
		}
		
		//Vérification du mot de passe
		if (! password_verify($pass, $utilisateur->pass))
		{
			//Mot de passe erroné
			$this->session->set('err', $this->t['erreur_pass']);
			$this->response->redirect('login');
			return false;
		}
		
		//Mise à jour de l'utilisateur
		$utilisateur->dernier_login = time();
		$utilisateur->ip_dernier = $_SERVER['REMOTE_ADDR'];
		
		$saved = $utilisateur->save();
		
		if ($saved == false)
		{
			//Erreur à la mise à jour de l'utilisateur
			$this->session->set('err', $this->t['erreur_maj']);
			$this->response->redirect('login');
			return false;
		}
		
		//Mise en session des informations de l'utilisateur
		$this->session->set('u_id', $utilisateur->id);
		$this->session->set('u_id_societe', $utilisateur->id_societe);
		$this->session->set('u_nom', $utilisateur->nom);
		$this->session->set('u_prenom', $utilisateur->prenom);
		$this->session->set('u_email', $utilisateur->email);
		$this->session->set('u_dernier', $utilisateur->dernier_login);
		$this->session->set('u_ip', $utilisateur->ip_dernier);
		$this->session->set('u_fonction', $utilisateur->fonction);
		$this->session->set('u_vignette', $utilisateur->vignette);
		$this->session->set('u_statut', $utilisateur->statut);
		
		$this->log('');
		
		//Redirection vers le tableau de bord
		$this->response->redirect('index');
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
            $this->loadCustomTrans('login');
        }

        //Retour à la dernière page
        $referer = $this->request->getHTTPReferer();
        if (strpos($referer, $this->request->getHttpHost()."/")!==false) {
            return $this->response->setHeader("Location", $referer);
        } else {
            return $this->dispatcher->forward(array('controller' => 'login', 'action' => 'index'));
        }
    }
}
