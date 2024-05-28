/* tslint:disable:no-unused-variable */
import { async, ComponentFixture, TestBed } from '@angular/core/testing';
import { By } from '@angular/platform-browser';
import { DebugElement } from '@angular/core';

import { FbShareComponent } from './fb-share.component';

describe('FbShareComponent', () => {
  let component: FbShareComponent;
  let fixture: ComponentFixture<FbShareComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ FbShareComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(FbShareComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
