<?php

namespace App;

/**
 * Attribut et méthodes commune aux class ForeignKey, MultileForeignKeys
 * 
 * @author  Jérémy Vaud
 */
trait ForeignTrait {
    protected string $column = "id"; // Nom de la colone à afficher dans panneau d'administration

    /**
     * Retourne l'attribut column
     *
     * @return string
     */
    public function getColumn(): string {
        return $this->column;
    }
}
