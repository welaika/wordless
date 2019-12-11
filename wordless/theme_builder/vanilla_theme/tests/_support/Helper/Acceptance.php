<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{
    public function _before(\Codeception\TestInterface $test)
    {
        // Dopo ore di battaglia, ho capito che tra uno scenario e l'altro, WP
        // si stava tenendo nella sua object cache in memoria i risultati di alcune query.
        // Per esempio se
        // * in uno scenario aggiungo un post che verrà creato con ID 1
        // * svolgo i test
        // * passo allo scenario successivo
        // * il DB viene resettato
        // * creo un nuovo post ed esso prenderà nuovamente ID 1
        // * ripetendo nuovamente la query per prendere il post con ID 1, WP lo prenderà dalla
        //   sua cache interna
        //
        // Uso quindi l'hook dell'helper degli acceptance per svuotare la cache di WP prima
        // di ogni test.
        wp_cache_flush();
    }
}
