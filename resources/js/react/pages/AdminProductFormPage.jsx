import React, { useEffect, useState } from 'react';
import DashboardShell from '../components/admin/DashboardShell';
import { FieldError, FlashMessages } from '../components/admin/common';

function ProductImagePreview({ currentImageUrl, previewUrl, mode }) {
    const imageUrl = previewUrl || currentImageUrl;

    return (
        <aside className="fit-product-form-preview">
            <div className="fit-product-form-preview__image">
                {imageUrl ? (
                    <img src={imageUrl} alt="Previsualizacion del producto" />
                ) : (
                    <div>
                        <i className="ri-image-add-line" />
                        <span>Sin imagen</span>
                    </div>
                )}
            </div>
            <div className="fit-product-form-preview__copy">
                <strong>Previsualizacion</strong>
                <span>{mode === 'edit' ? 'Imagen actual o nueva seleccionada.' : 'Selecciona una imagen para revisarla antes de guardar.'}</span>
            </div>
        </aside>
    );
}

export default function AdminProductFormPage({ layout, data, flash, errors, old, csrfToken, logoutAction }) {
    const product = data.product || null;
    const isEdit = data.mode === 'edit';
    const [previewUrl, setPreviewUrl] = useState(null);

    useEffect(() => () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }
    }, [previewUrl]);

    const value = (field, fallback = '') => old?.[field] ?? product?.[field] ?? fallback;
    const activeValue = String(value('is_active', 1)) === '0' ? '0' : '1';

    const handleImageChange = (event) => {
        const file = event.target.files?.[0];

        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
        }

        setPreviewUrl(file ? URL.createObjectURL(file) : null);
    };

    return (
        <DashboardShell sidebar={layout.sidebar} topbar={layout.topbar} csrfToken={csrfToken} logoutAction={logoutAction}>
            <div className="fit-users-page fit-products-page fit-product-form-page">
                <FlashMessages flash={flash} />

                <section className="fit-users-header">
                    <div className="fit-users-header-left">
                        <div className="fit-header-icon"><i className={isEdit ? 'ri-edit-2-line' : 'ri-add-box-line'} /></div>
                        <div>
                            <h1>{isEdit ? 'Editar producto' : 'Crear producto'}</h1>
                            <p>{isEdit ? 'Actualiza la informacion comercial, inventario base e imagen del producto.' : 'Registra el producto con sus precios, stock base e imagen para el catalogo.'}</p>
                        </div>
                    </div>
                    <div className="fit-users-header-actions">
                        <a className="fit-outline-button" href={data.routes.index}>
                            <i className="ri-arrow-left-line" />
                            <span>Volver al catalogo</span>
                        </a>
                    </div>
                </section>

                <form method="POST" action={isEdit ? data.routes.update : data.routes.store} className="fit-product-form-shell" encType="multipart/form-data">
                    <input type="hidden" name="_token" value={csrfToken} />
                    {isEdit && <input type="hidden" name="_method" value="PUT" />}

                    <div className="fit-product-form-main">
                        <section className="fit-product-form-card">
                            <div className="fit-section-head compact">
                                <div>
                                    <h2>Informacion principal</h2>
                                    <p>Datos visibles en catalogo, ventas y cotizaciones.</p>
                                </div>
                            </div>
                            <div className="fit-form-grid">
                                <div className="fit-form-field">
                                    <label htmlFor="category_id">Categoria *</label>
                                    <select id="category_id" name="category_id" defaultValue={value('category_id')} required>
                                        <option value="">Selecciona una categoria</option>
                                        {data.categories.map((category) => <option key={category.id} value={category.id}>{category.name}</option>)}
                                    </select>
                                    <FieldError errors={errors} name="category_id" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="name">Nombre *</label>
                                    <input id="name" type="text" name="name" placeholder="Ej. Leche entera 1L" defaultValue={value('name')} required />
                                    <FieldError errors={errors} name="name" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="sku">SKU / Codigo *</label>
                                    <input id="sku" type="text" name="sku" placeholder="Ej. PIL-001" defaultValue={value('sku')} required />
                                    <FieldError errors={errors} name="sku" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="is_active">Estado *</label>
                                    <select id="is_active" name="is_active" defaultValue={activeValue} required>
                                        <option value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                    <FieldError errors={errors} name="is_active" />
                                </div>

                                <div className="fit-form-field span-2">
                                    <label htmlFor="description">Descripcion</label>
                                    <textarea id="description" name="description" rows="5" defaultValue={value('description')} />
                                    <FieldError errors={errors} name="description" />
                                </div>
                            </div>
                        </section>

                        <section className="fit-product-form-card">
                            <div className="fit-section-head compact">
                                <div>
                                    <h2>Precios e inventario</h2>
                                    <p>Valores usados para ventas, cotizaciones y alertas operativas.</p>
                                </div>
                            </div>
                            <div className="fit-form-grid">
                                <div className="fit-form-field">
                                    <label htmlFor="suggested_price_public">Precio publico *</label>
                                    <input id="suggested_price_public" type="number" step="0.01" min="0" name="suggested_price_public" defaultValue={value('suggested_price_public')} required />
                                    <FieldError errors={errors} name="suggested_price_public" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="price_institutional">Precio institucional *</label>
                                    <input id="price_institutional" type="number" step="0.01" min="0" name="price_institutional" defaultValue={value('price_institutional')} required />
                                    <FieldError errors={errors} name="price_institutional" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="min_quantity">Stock minimo *</label>
                                    <input id="min_quantity" type="number" min="0" name="min_quantity" defaultValue={value('min_quantity', 0)} required />
                                    <FieldError errors={errors} name="min_quantity" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="max_quantity">Stock maximo *</label>
                                    <input id="max_quantity" type="number" min="0" name="max_quantity" defaultValue={value('max_quantity', 0)} required />
                                    <FieldError errors={errors} name="max_quantity" />
                                </div>
                            </div>
                        </section>

                        <section className="fit-product-form-card">
                            <div className="fit-section-head compact">
                                <div>
                                    <h2>Imagen y bitacora</h2>
                                    <p>Adjunta una imagen clara y registra el motivo administrativo del cambio.</p>
                                </div>
                            </div>
                            <div className="fit-form-grid">
                                <div className="fit-form-field">
                                    <label htmlFor="image">{isEdit ? 'Imagen nueva' : 'Imagen del producto *'}</label>
                                    <input id="image" type="file" name="image" accept="image/*" required={!isEdit} onChange={handleImageChange} />
                                    <FieldError errors={errors} name="image" />
                                </div>

                                <div className="fit-form-field">
                                    <label htmlFor="audit_reason">Motivo para bitacora</label>
                                    <textarea id="audit_reason" name="audit_reason" rows="3" placeholder="Ej. Actualizacion de precio autorizada" defaultValue={old?.audit_reason ?? ''} />
                                    <FieldError errors={errors} name="audit_reason" />
                                </div>
                            </div>
                        </section>
                    </div>

                    <div className="fit-product-form-side">
                        <ProductImagePreview currentImageUrl={product?.image_url} previewUrl={previewUrl} mode={data.mode} />
                        <div className="fit-product-form-actions">
                            <a className="fit-outline-button" href={data.routes.index}>Cancelar</a>
                            <button type="submit" className="fit-primary-button">
                                <i className={isEdit ? 'ri-save-3-line' : 'ri-checkbox-circle-line'} />
                                <span>{isEdit ? 'Guardar cambios' : 'Registrar producto'}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </DashboardShell>
    );
}
