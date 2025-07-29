import { ComponentFixture, TestBed } from '@angular/core/testing';

import { UserLessons } from './user-lessons';

describe('UserLessons', () => {
  let component: UserLessons;
  let fixture: ComponentFixture<UserLessons>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [UserLessons]
    })
    .compileComponents();

    fixture = TestBed.createComponent(UserLessons);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
