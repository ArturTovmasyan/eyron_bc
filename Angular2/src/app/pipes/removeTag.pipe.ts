import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: 'removeTag'})
export class RemoveTagPipe implements PipeTransform {
    transform(value: string, args: string[]): any {
        if (!value) return value;

        return value.replace(/#([^\s#])/, '$1');
    }
}
