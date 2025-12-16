import { ComponentFixture, TestBed } from '@angular/core/testing';

import { BannerManager } from './banner-manager';

describe('BannerManager', () => {
  let component: BannerManager;
  let fixture: ComponentFixture<BannerManager>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [BannerManager]
    })
    .compileComponents();

    fixture = TestBed.createComponent(BannerManager);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
