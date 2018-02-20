<?php
/**
 * Auteur: SOBANDITH Jimmy
 * Société: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Modèle de données de la table des évènements
 */
class Evenements extends \Phalcon\Mvc\Model
{
    public $id;
    public $nom;
    public $ville;
    public $date;
    public $type;



    /**
     * Mapping du modèle avec la table de la base de données
     */
    public function getSource()
    {
        return 'evenements';
    }
	
	public function initialize()
    {
		$this->hasMany('id', 'Logs', 'id_utilisateur');
		$this->hasMany('id', 'Taches', 'id_utilisateur');
		$this->hasMany('id', 'Fitres', 'id_utilisateur');
    }
}
?>