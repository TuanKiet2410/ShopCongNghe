import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Permissionmanager } from './permissionmanager';

describe('Permissionmanager', () => {
  let component: Permissionmanager;
  let fixture: ComponentFixture<Permissionmanager>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Permissionmanager]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Permissionmanager);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
