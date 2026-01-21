import { ComponentFixture, TestBed } from '@angular/core/testing';

import { Promotionmanager } from './promotionmanager';

describe('Promotionmanager', () => {
  let component: Promotionmanager;
  let fixture: ComponentFixture<Promotionmanager>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Promotionmanager]
    })
    .compileComponents();

    fixture = TestBed.createComponent(Promotionmanager);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
