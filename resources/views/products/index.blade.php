<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C7pourt3 — Gestion du Catalogue</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;1,600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        .serif-title {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        .table-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            border: 1px solid #e9ecef;
        }
        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .nav-brand-lux {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <!-- Barre de Navigation Blanche & Moderne -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand nav-brand-lux text-dark text-uppercase" href="{{ route('dashboard') }}">C7POURT3</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-secondary" href="{{ route('dashboard') }}">Centre de Contrôle</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active fw-semibold text-dark" href="{{ route('products.index') }}">Gestion Catalogue</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="text-secondary small me-3">Bonjour, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Déconnexion</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        
        <!-- Messages flash de succès -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <svg class="me-2" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4 rounded-3" role="alert">
                <div class="d-flex align-items-center">
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- En-tête -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5">
            <div>
                <h1 class="serif-title text-dark mb-1">Catalogue C7Pourt3</h1>
                <p class="text-muted mb-0">Gestion des sacs affichés sur le site e-commerce c7-pourt3.vercel.app.</p>
            </div>
            <button type="button" class="btn btn-dark px-4 py-2 mt-3 mt-md-0 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createProductModal">
                + Nouveau Produit
            </button>
        </div>

        <!-- Tableau des Produits -->
        <div class="table-container shadow-sm border border-light">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr class="text-secondary small fw-semibold">
                            <th class="py-3 px-3">IDENTIFIANT</th>
                            <th class="py-3 px-3">Aperçu</th>
                            <th class="py-3 px-3">Catégorie & Nom</th>
                            <th class="py-3 px-3">Prix (XAF)</th>
                            <th class="py-3 px-3">Prix (MAD)</th>
                            <th class="py-3 px-3">Statut</th>
                            <th class="py-3 px-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-bottom">
                                <td class="py-4 px-3 text-secondary fw-semibold">
                                    # {{ $loop->iteration }}
                                </td>
                                <td class="py-4 px-3">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-thumbnail">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light text-secondary rounded-3" style="width: 80px; height: 80px; border: 1px solid #dee2e6;">
                                            Pas d'image
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-3">
                                    <div class="fw-bold text-dark">{{ $product->model }}</div>
                                    <div class="text-secondary small">{{ $product->name }}</div>
                                </td>
                                <td class="py-4 px-3 fw-semibold text-dark">
                                    {{ number_format($product->price_xaf, 0, ',', ' ') }} XAF
                                </td>
                                <td class="py-4 px-3 text-secondary">
                                    {{ number_format($product->price_mad, 0, ',', ' ') }} MAD
                                </td>
                                <td class="py-4 px-3">
                                    @if($product->stock_libreville > 5)
                                        <span class="badge rounded-pill bg-success text-white px-3 py-2">En stock ({{ $product->stock_libreville }})</span>
                                    @elseif($product->stock_libreville > 0)
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-2">Stock faible ({{ $product->stock_libreville }})</span>
                                    @else
                                        <span class="badge rounded-pill bg-danger text-white px-3 py-2">Rupture</span>
                                    @endif
                                </td>
                                <td class="py-4 px-3 text-end">
                                    <div class="btn-group shadow-sm">
                                        <button type="button" class="btn btn-sm btn-outline-secondary edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editProductModal"
                                            data-id="{{ $product->id }}"
                                            data-name="{{ $product->name }}"
                                            data-model="{{ $product->model }}"
                                            data-price-xaf="{{ $product->price_xaf }}"
                                            data-price-mad="{{ $product->price_mad }}"
                                            data-stock="{{ $product->stock_libreville }}"
                                            data-image-url="{{ $product->image_url }}">
                                            Modifier
                                        </button>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce sac du catalogue Firebase ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-5 text-center text-secondary">
                                    <div class="my-4">
                                        <p class="mb-1 fw-semibold">Aucun sac enregistré dans le catalogue</p>
                                        <p class="small text-muted">Utilisez le bouton "+ Nouveau Produit" pour en ajouter un.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL D'AJOUT PRODUIT -->
    <div class="modal fade" id="createProductModal" tabindex="-1" aria-labelledby="createProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title serif-title text-dark" id="createProductModalLabel">Ajouter un Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body px-4 py-4">
                        <div class="mb-3">
                            <label for="create_identifiant" class="form-label small fw-semibold text-secondary">Identifiant unique (ID Firebase)</label>
                            <input type="text" class="form-control rounded-3" id="create_identifiant" name="identifiant" placeholder="Ex: sac-croco-noir (sans espaces)" required>
                            <div class="form-text text-muted small">Cet ID sera utilisé comme clé du document sur Firebase.</div>
                        </div>
                        <div class="mb-3">
                            <label for="create_model" class="form-label small fw-semibold text-secondary">Catégorie</label>
                            <input type="text" class="form-control rounded-3" id="create_model" name="model" placeholder="Ex: Soirée, Quotidien, Luxe" required>
                        </div>
                        <div class="mb-3">
                            <label for="create_name" class="form-label small fw-semibold text-secondary">Nom du sac</label>
                            <input type="text" class="form-control rounded-3" id="create_name" name="name" placeholder="Ex: Saddle Bag Noir, Boy" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="create_price_xaf" class="form-label small fw-semibold text-secondary">Prix (XAF)</label>
                                <input type="number" class="form-control rounded-3" id="create_price_xaf" name="price_xaf" placeholder="Ex: 45000" min="0" required>
                            </div>
                            <div class="col">
                                <label for="create_price_mad" class="form-label small fw-semibold text-secondary">Prix (MAD)</label>
                                <input type="number" class="form-control rounded-3" id="create_price_mad" name="price_mad" placeholder="Ex: 750" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="create_stock" class="form-label small fw-semibold text-secondary">Stock Libreville</label>
                            <input type="number" class="form-control rounded-3" id="create_stock" name="stock_libreville" placeholder="Ex: 10" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="create_image_url" class="form-label small fw-semibold text-secondary">Lien image (Optionnel)</label>
                            <input type="url" class="form-control rounded-3" id="create_image_url" name="image_url" placeholder="https://lien-image.com/sac.jpg">
                        </div>
                        <div class="mb-3">
                            <label for="create_image_file" class="form-label small fw-semibold text-secondary">Ou télécharger l'image (Optionnel)</label>
                            <input type="file" class="form-control rounded-3" id="create_image_file" name="image_file" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark rounded-3 px-4">Créer le sac</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL DE MODIFICATION PRODUIT -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title serif-title text-dark" id="editProductModalLabel">Modifier le Produit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4 py-4">
                        <div class="mb-3">
                            <label for="edit_model" class="form-label small fw-semibold text-secondary">Catégorie</label>
                            <input type="text" class="form-control rounded-3" id="edit_model" name="model" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_name" class="form-label small fw-semibold text-secondary">Nom du sac</label>
                            <input type="text" class="form-control rounded-3" id="edit_name" name="name" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label for="edit_price_xaf" class="form-label small fw-semibold text-secondary">Prix (XAF)</label>
                                <input type="number" class="form-control rounded-3" id="edit_price_xaf" name="price_xaf" min="0" required>
                            </div>
                            <div class="col">
                                <label for="edit_price_mad" class="form-label small fw-semibold text-secondary">Prix (MAD)</label>
                                <input type="number" class="form-control rounded-3" id="edit_price_mad" name="price_mad" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_stock" class="form-label small fw-semibold text-secondary">Stock Libreville</label>
                            <input type="number" class="form-control rounded-3" id="edit_stock" name="stock_libreville" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_url" class="form-label small fw-semibold text-secondary">Lien image (Optionnel)</label>
                            <input type="url" class="form-control rounded-3" id="edit_image_url" name="image_url">
                        </div>
                        <div class="mb-3">
                            <label for="edit_image_file" class="form-label small fw-semibold text-secondary">Ou télécharger une nouvelle image (Optionnel)</label>
                            <input type="file" class="form-control rounded-3" id="edit_image_file" name="image_file" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-top px-4 py-3">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-dark rounded-3 px-4">Sauvegarder</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Script JS pour l'injection des valeurs dans le Modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-btn');
            const editForm = document.getElementById('editProductForm');
            
            const editModelInput = document.getElementById('edit_model');
            const editNameInput = document.getElementById('edit_name');
            const editPriceXafInput = document.getElementById('edit_price_xaf');
            const editPriceMadInput = document.getElementById('edit_price_mad');
            const editStockInput = document.getElementById('edit_stock');
            const editImageUrlInput = document.getElementById('edit_image_url');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const model = this.getAttribute('data-model');
                    const priceXaf = this.getAttribute('data-price-xaf');
                    const priceMad = this.getAttribute('data-price-mad');
                    const stock = this.getAttribute('data-stock');
                    const imageUrl = this.getAttribute('data-image-url');

                    // Définir dynamiquement l'action du formulaire avec l'ID du produit
                    editForm.action = `/products/${id}`;

                    // Remplir les inputs
                    editModelInput.value = model;
                    editNameInput.value = name;
                    editPriceXafInput.value = priceXaf;
                    editPriceMadInput.value = priceMad;
                    editStockInput.value = stock;
                    editImageUrlInput.value = imageUrl || '';
                });
            });
        });
    </script>
</body>
</html>
