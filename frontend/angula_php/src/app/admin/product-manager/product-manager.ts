import { Component, computed, effect, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';

import { ProductPtService } from '../../phantrang/product-pt-service';
import { ProductInterface } from '../../interface/product-interface';

declare var bootstrap: any;

@Component({
  selector: 'app-product-manager',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './product-manager.html',
  styleUrl: './product-manager.css',
})
export class ProductManagerComponent implements OnInit {

  router = inject(Router);
  productService = inject(ProductPtService);

  // 🔥 DÙNG TRỰC TIẾP SIGNAL
  products = computed(() => this.productService.products_signal());

  // ===== FILTER STATE =====
  searchText = '';
  selectedCategory = '';
  selectedBrand = '';
  selectedPriceRange = '';

  categories = ['Điện thoại', 'Laptop', 'Tablet', 'Phụ kiện'];
  brands = ['Apple', 'Samsung', 'Dell', 'Sony'];

  // ===== PAGINATION =====
  currentPage = 1;
  totalPages = 0;

  get pageNumbers(): number[] {
    return Array.from({ length: this.totalPages }, (_, i) => i + 1);
  }

  // ===== FORM =====
  productForm: FormGroup;
  isEditMode = false;
  editId = 0;

  constructor(private fb: FormBuilder) {
    this.productForm = this.fb.group({
      name: ['', Validators.required],
      price: [0, [Validators.required, Validators.min(0)]],
      description: [''],
      image: [''],
      category: [''],
      brand: [''],
      stock: [0, [Validators.required, Validators.min(0)]],
    });

    // 🔁 Đồng bộ pagination khi API trả về
    effect(() => {
      this.currentPage = this.productService.currentPageSignal();
      this.totalPages = this.productService.totalPagesSignal();
      console.log(this.totalPages);
    });
  }

  ngOnInit(): void {
    if (localStorage.getItem('token')) {
      this.loadProducts(1);
    }
  }

  // ================= API CALL =================

  loadProducts(page = 1) {
    this.productService.loadProducts(
      page,
      this.searchText,
      this.selectedCategory,
      this.selectedBrand,
      this.selectedPriceRange
    );
  }

  onSearch() {
    this.loadProducts(1);
  }

  applyFilter() {
    this.loadProducts(1);
  }

  goToPage(page: number) {
    if (page >= 1 && page <= this.totalPages) {
      this.loadProducts(page);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  }

  resetFilter() {
    this.searchText = '';
    this.selectedCategory = '';
    this.selectedBrand = '';
    this.selectedPriceRange = '';
    this.loadProducts(1);
  }

  // ================= CRUD =================

  submitForm() {
    if (this.productForm.invalid) return;

    if (this.isEditMode) {
      this.productService.update(this.editId, this.productForm.value);
    } else {
      this.productService.create(this.productForm.value);
    }

    this.closeModal();
    this.loadProducts(this.currentPage);
  }

  deleteProduct(id: number) {
    if (confirm('Xóa sản phẩm này?')) {
      this.productService.delete(id);
      this.loadProducts(this.currentPage);
    }
  }

  editProduct(product: ProductInterface) {
    this.productForm.patchValue(product);
    this.editId = product.id!;
    this.isEditMode = true;
    this.openModal();
  }

  // ================= MODAL =================

  openModal() {
    const modal = new bootstrap.Modal(document.getElementById('modalId'));
    modal.show();
  }

  closeModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalId'));
    modal?.hide();
    this.productForm.reset();
    this.isEditMode = false;
  }

  // ================= IMAGE =================

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    if (!input.files?.length) return;

    const reader = new FileReader();
    reader.onload = () => {
      this.productForm.patchValue({ image: reader.result });
    };
    reader.readAsDataURL(input.files[0]);
  }

  trackById(index: number, item: ProductInterface): number {
  return item.id!;
}

}
