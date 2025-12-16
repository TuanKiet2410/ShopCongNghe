import { TestBed } from '@angular/core/testing';

import { ProductPtService } from './product-pt-service';

describe('ProductPtService', () => {
  let service: ProductPtService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(ProductPtService);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
