import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';

@Component({
  selector: 'app-role-manager',
  imports: [ReactiveFormsModule],
  templateUrl: './role-manager.html',
  styleUrl: './role-manager.css',
})
export class RoleManagerComponent {
  
  permissionForm: FormGroup;


  constructor(private fb: FormBuilder) {
    this.permissionForm = this.fb.group({
      admin_user: [{ value: true, disabled: true }],
      employer_user: true,
      customer_user: true,

      admin_product: [{ value: true, disabled: true }],
      employer_product: true,
      customer_product: true,

      admin_order: true,
      employer_order: true,
      customer_order: true
    });
  }

  onSave() {
    // getRawValue lấy luôn checkbox disabled
    const permissions = this.permissionForm.getRawValue();
    console.log(permissions);

    /*
    {
      admin_user: true,
      employer_user: false,
      customer_user: false,
      admin_product: true,
      employer_product: true,
      customer_product: false,
      admin_order: true,
      employer_order: true,
      customer_order: true
    }
    */
  }
}
