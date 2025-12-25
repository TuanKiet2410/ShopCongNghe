import { TestBed } from '@angular/core/testing';
import { CanActivateFn } from '@angular/router';

import { buyGuard } from './buy-guard';

describe('buyGuard', () => {
  const executeGuard: CanActivateFn = (...guardParameters) => 
      TestBed.runInInjectionContext(() => buyGuard(...guardParameters));

  beforeEach(() => {
    TestBed.configureTestingModule({});
  });

  it('should be created', () => {
    expect(executeGuard).toBeTruthy();
  });
});
