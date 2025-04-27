<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    #[Route('/add-to-panier/{product}', name: 'add_to_panier')]
    public function addToPanier(Request $request, string $product): Response
    {
        $products = [
            'smartphone' => [
                'name' => 'Smartphone Premium',
                'price' => 599.99,
                'image' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            ],
            'watch' => [
                'name' => 'Montre Intelligente',
                'price' => 199.99,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            ],
            'headphones' => [
                'name' => 'Écouteurs Sans Fil',
                'price' => 149.99,
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=80'
            ],
        ];

        // Validate and check if product exists
        if (!isset($products[$product])) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $selectedProduct = $products[$product];

        // Ensure selected product has the required structure (extra validation)
        if (!isset($selectedProduct['name'], $selectedProduct['price'], $selectedProduct['image'])) {
            throw $this->createNotFoundException('Structure invalide pour le produit sélectionné.');
        }

        // Get the current cart (panier) from session or initialize a new cart
        $panier = $request->getSession()->get('panier', []);
        $panier[] = $selectedProduct; // Add the selected product to the cart
        $request->getSession()->set('panier', $panier); // Save cart back into session

        $this->addFlash('success', 'Produit ajouté au panier !');
        return $this->redirectToRoute('app_panier');

    }

    #[Route('/panier', name: 'app_panier')]
    public function panier(Request $request): Response
    {
        $panier = $request->getSession()->get('panier', []);

        // Debugging: Trace the structure of the current cart
        // Uncomment this during development to inspect the cart structure
        // dump($panier);

        return $this->render('panier/index.html.twig', [
            'panier' => $panier
        ]);
    }

    #[Route('/clear-panier', name: 'clear_panier')]
    public function clearPanier(Request $request): Response
    {
        $request->getSession()->remove('panier');

        $this->addFlash('success', 'Panier vidé avec succès.');
        return $this->redirectToRoute('app_panier');
    }

    #[Route('/remove-from-panier/{index}', name: 'remove_from_panier')]
    public function removeFromPanier(Request $request, int $index): Response
    {
        $panier = $request->getSession()->get('panier', []);

        // Ensure the index exists in the cart before trying to remove it
        if (isset($panier[$index])) {
            unset($panier[$index]);

            // Re-index the array after removal to avoid issues with array keys
            $panier = array_values($panier);
            $request->getSession()->set('panier', $panier);

            $this->addFlash('success', 'Produit supprimé du panier avec succès.');
        } else {
            $this->addFlash('error', 'Impossible de supprimer ce produit, il n\'existe pas dans le panier.');
        }

        return $this->redirectToRoute('app_panier');
    }
}