import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: 'sliceEmail'})
export class SliceEmailPipe implements PipeTransform {
    transform(value: string, args: string[]): any {
        if (!value) return value;

        let slice = value.split('@');
        let email = '...@' + slice[1];

        return email;
    }
}
