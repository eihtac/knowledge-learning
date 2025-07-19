import { TestBed } from '@angular/core/testing';

import { FlashMessage } from './flash-message';

describe('FlashMessage', () => {
  let service: FlashMessage;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(FlashMessage);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
