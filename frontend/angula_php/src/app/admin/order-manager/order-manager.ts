import { Component, computed, effect, inject } from '@angular/core';
import { InvoiceService } from '../../services/invoice/invoice-service';

@Component({
  selector: 'app-order-manager',
  imports: [],
  templateUrl: './order-manager.html',
  styleUrl: './order-manager.css',
})
export class OrderManagerComponent {
  invoiceService = inject(InvoiceService);
  invoices = computed(() => this.invoiceService.invoices_signal());
  constructor() { 
    effect(() => { this.loadInvoices(); });
   }

  loadInvoices() { return this.invoices(); }

  deleteInvoice(id: number) {
    this.invoiceService.deleteInvoice(id);
    this.loadInvoices();
  }

  updateStatusInvoice(id: number, invoice: any) {
    this.invoiceService.updateStatusInvoice(id, invoice);
    this.loadInvoices();
  }

  onChange(id: number, event: any) {
  const selectedValue = event.target.value;
  
  if (selectedValue === "paid" || selectedValue === "shipped" || selectedValue === "pending") {
    const invoice = this.invoices().find(inv => inv.id === id);
    if (invoice) {
      invoice.status = selectedValue;
      this.updateStatusInvoice(id, invoice);
    }
  }
}

}
