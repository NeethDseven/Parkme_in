<?php
class PaginationService {
    private $currentPage;
    private $totalItems;
    private $itemsPerPage;
    private $totalPages;
    
    /**
     * Initialise le service de pagination
     * 
     * @param int $totalItems Nombre total d'éléments
     * @param int $itemsPerPage Nombre d'éléments par page
     * @param int $currentPage Page actuelle (optionnel, par défaut: 1)
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage = 1) {
        $this->totalItems = max(0, (int)$totalItems);
        $this->itemsPerPage = max(1, (int)$itemsPerPage);
        $this->currentPage = max(1, (int)$currentPage);
        $this->totalPages = max(1, ceil($this->totalItems / $this->itemsPerPage));
        
        // S'assurer que la page actuelle ne dépasse pas le nombre total de pages
        $this->currentPage = min($this->currentPage, $this->totalPages);
    }
    
    /**
     * Calcule l'offset SQL pour la requête
     */
    public function getOffset() {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }
    
    /**
     * Retourne le nombre d'éléments par page
     */
    public function getLimit() {
        return $this->itemsPerPage;
    }
    
    /**
     * Retourne la page actuelle
     */
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Retourne le nombre total de pages
     */
    public function getTotalPages() {
        return $this->totalPages;
    }
    
    /**
     * Génère le HTML pour la pagination
     * 
     * @param string $baseUrl URL de base (sans paramètres de pagination)
     * @param array $queryParams Paramètres additionnels à conserver dans l'URL
     * @return string HTML de la pagination
     */
    public function createLinks($baseUrl, $params = []) {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $links = '<ul class="pagination">';
        
        // Bouton Précédent
        if ($this->currentPage > 1) {
            $prevParams = $params;
            $prevParams['p'] = $this->currentPage - 1;
            $queryString = http_build_query($prevParams);
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '/?' . $queryString . '">&laquo; Précédent</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><span class="page-link">&laquo; Précédent</span></li>';
        }
        
        // Pages numérotées
        $range = 2; // Nombre de pages à afficher de chaque côté de la page actuelle
        
        for ($i = max(1, $this->currentPage - $range); $i <= min($this->totalPages, $this->currentPage + $range); $i++) {
            $pageParams = $params;
            $pageParams['p'] = $i;
            $queryString = http_build_query($pageParams);
            
            if ($i == $this->currentPage) {
                $links .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '/?' . $queryString . '">' . $i . '</a></li>';
            }
        }
        
        // Bouton Suivant
        if ($this->currentPage < $this->totalPages) {
            $nextParams = $params;
            $nextParams['p'] = $this->currentPage + 1;
            $queryString = http_build_query($nextParams);
            $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '/?' . $queryString . '">Suivant &raquo;</a></li>';
        } else {
            $links .= '<li class="page-item disabled"><span class="page-link">Suivant &raquo;</span></li>';
        }
        
        $links .= '</ul>';
        
        return $links;
    }
    
    /**
     * Construit une URL avec les paramètres donnés
     */
    private function buildUrl($baseUrl, $params) {
        // Assurer que page et action sont conservés, mais éviter les duplications
        if (!isset($params['page']) && isset($_GET['page'])) {
            $params['page'] = $_GET['page'];
        }
        if (!isset($params['action']) && isset($_GET['action'])) {
            $params['action'] = $_GET['action'];
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }
}
