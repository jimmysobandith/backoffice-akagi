<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Modèle de données de la table des utilisateurs
 */
class Utilisateurs extends \Phalcon\Mvc\Model
{
    public $id;
	public $id_societe;
	public $date;
	public $nom;
	public $prenom;
	public $email;
	public $pass;
	public $pass2;
	public $dernier_login;
	public $ip_dernier;
	public $fonction;
	public $vignette;
	public $flags;
	public $todo;
	public $actif;

    /**
     * Mapping du modèle avec la table de la base de données
     */
    public function getSource()
    {
        return 'utilisateurs';
    }
	
	public function initialize()
    {
		$this->hasMany('id', 'Logs', 'id_utilisateur');
		$this->hasMany('id', 'Taches', 'id_utilisateur');
		$this->hasMany('id', 'Fitres', 'id_utilisateur');
    }
}
?>