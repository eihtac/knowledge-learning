import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class FlashMessage {
  private message: string | null = null;

  setMessage(msg: string): void {
    this.message = msg;
  }

  getMessage(): string | null {
    const temp = this.message;
    this.message = null;
    return temp;
  }
}
