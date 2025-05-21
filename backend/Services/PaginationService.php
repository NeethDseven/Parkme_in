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
        $this->totalItems = (int)$totalItems;
        $this->itemsPerPage = (int)$itemsPerPage;
        $this->totalPages = ceil($this->totalItems / $this->itemsPerPage);
        $this->currentPage = $this->validatePageNumber($currentPage);
    }
    
    /**
     * S'assure que le numéro de page est valide
     */
    private function validatePageNumber($page) {
        $page = (int)$page;
        if ($page < 1) {
            return 1;
        }
        if ($page > $this->totalPages && $this->totalPages > 0) {
            return $this->totalPages;
        }
        return $page;
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
    public function createLinks($baseUrl, $queryParams = []) {
        if ($this->totalPages <= 1) {
            return '';
        }
        
        $html = '<nav aria-label="Navigation des pages"><ul class="pagination">';
        
        // Lien précédent
        if ($this->currentPage > 1) {
            $params = array_merge($queryParams, ['p' => $this->currentPage - 1]);
            $prevUrl = $this->buildUrl($baseUrl, $params);
            $html .= '<li class="page-item"><a class="page-link" href="' . $prevUrl . '" aria-label="Précédent">&laquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
        }
        
        // Liens des pages
        $visiblePages = 5; // Nombre de pages visibles autour de la page courante
        $halfVisible = floor($visiblePages / 2);
        
        $startPage = max(1, $this->currentPage - $halfVisible);
        $endPage = min($this->totalPages, $startPage + $visiblePages - 1);
        
        if ($endPage - $startPage + 1 < $visiblePages) {
            $startPage = max(1, $endPage - $visiblePages + 1);
        }
        
        if ($startPage > 1) {
            $params = array_merge($queryParams, ['p' => 1]);
            $firstUrl = $this->buildUrl($baseUrl, $params);
            $html .= '<li class="page-item"><a class="page-link" href="' . $firstUrl . '">1</a></li>';
            if ($startPage > 2) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $params = array_merge($queryParams, ['p' => $i]);
            $pageUrl = $this->buildUrl($baseUrl, $params);
            
            if ($i == $this->currentPage) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . $pageUrl . '">' . $i . '</a></li>';
            }
        }
        
        if ($endPage < $this->totalPages) {
            if ($endPage < $this->totalPages - 1) {
                $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            $params = array_merge($queryParams, ['p' => $this->totalPages]);
            $lastUrl = $this->buildUrl($baseUrl, $params);
            $html .= '<li class="page-item"><a class="page-link" href="' . $lastUrl . '">' . $this->totalPages . '</a></li>';
        }
        
        // Lien suivant
        if ($this->currentPage < $this->totalPages) {
            $params = array_merge($queryParams, ['p' => $this->currentPage + 1]);
            $nextUrl = $this->buildUrl($baseUrl, $params);
            $html .= '<li class="page-item"><a class="page-link" href="' . $nextUrl . '" aria-label="Suivant">&raquo;</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
        }
        
        $html .= '</ul></nav>';
        
        return $html;
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
