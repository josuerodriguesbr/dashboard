<?php
// app/Utils/MenuHelper.php
namespace App\Utils;

class MenuHelper
{
    public static function getMenuItems($userLevel)
    {
        $menus = [
            'admin' => [
                [
                    'title' => 'Dashboard',
                    'url' => '/projetos/dashboard/admin',
                    'icon' => '🏠',
                    'permission' => 'admin'
                ],
                [
                    'title' => 'Gerenciar Usuários',
                    'url' => '/projetos/dashboard/admin/usuarios',
                    'icon' => '👥',
                    'permission' => 'admin'
                ],
                [
                    'title' => 'Logs do Sistema',
                    'url' => '/projetos/dashboard/server-logs',
                    'icon' => '📝',
                    'permission' => 'admin'
                ],
                [
                    'title' => 'Monitoramento DB',
                    'url' => '/projetos/dashboard/db-monitor',
                    'icon' => '📊',
                    'permission' => 'admin'
                ],
                [
                    'title' => 'Frontend Playground',
                    'url' => '/projetos/dashboard/frontend',
                    'icon' => '💻',
                    'permission' => 'admin'
                ]
            ],
            
            'assinante' => [
                [
                    'title' => 'Dashboard',
                    'url' => '/projetos/dashboard/assinante',
                    'icon' => '🏠',
                    'permission' => 'assinante'
                ],
                [
                    'title' => 'Meus Créditos',
                    'url' => '/projetos/dashboard/creditos',
                    'icon' => '💰',
                    'permission' => 'assinante'
                ],
                [
                    'title' => 'Meus Vendedores',
                    'url' => '/projetos/dashboard/assinante/vendedores',
                    'icon' => '👥',
                    'permission' => 'assinante'
                ],
                [
                    'title' => 'Transações',
                    'url' => '/projetos/dashboard/creditos/extrato',
                    'icon' => '💳',
                    'permission' => 'assinante'
                ]
            ],
            
            'vendedor' => [
                [
                    'title' => 'Dashboard',
                    'url' => '/projetos/dashboard/vendedor',
                    'icon' => '🏠',
                    'permission' => 'vendedor'
                ],
                [
                    'title' => 'Meus Créditos',
                    'url' => '/projetos/dashboard/creditos',
                    'icon' => '💰',
                    'permission' => 'vendedor'
                ],
                [
                    'title' => 'Clientes Associados',
                    'url' => '/projetos/dashboard/vendedor/clientes',
                    'icon' => '👥',
                    'permission' => 'vendedor'
                ],
                [
                    'title' => 'Extrato',
                    'url' => '/projetos/dashboard/creditos/extrato',
                    'icon' => '📋',
                    'permission' => 'vendedor'
                ]
            ],
            
            'cliente' => [
                [
                    'title' => 'Dashboard',
                    'url' => '/projetos/dashboard/cliente',
                    'icon' => '🏠',
                    'permission' => 'cliente'
                ],
                [
                    'title' => 'Meu Perfil',
                    'url' => '/projetos/dashboard/perfil',
                    'icon' => '👤',
                    'permission' => 'cliente'
                ]
            ]
        ];
        
        return $menus[$userLevel] ?? $menus['cliente'];
    }
    
    public static function renderMenuItems($userLevel)
    {
        $items = self::getMenuItems($userLevel);
        $currentPath = $_SERVER['REQUEST_URI'] ?? '';
        
        foreach ($items as $item) {
            $isActive = strpos($currentPath, $item['url']) !== false;
            $activeClass = $isActive ? 'active' : '';
            
            echo '<li class="menu-item">';
            echo '<a href="' . $item['url'] . '" class="menu-link ' . $activeClass . '">';
            echo '<span class="menu-icon">' . $item['icon'] . '</span>';
            echo '<span class="menu-text">' . htmlspecialchars($item['title']) . '</span>';
            echo '</a>';
            echo '</li>';
        }
    }
}