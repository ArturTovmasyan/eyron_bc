import { Pipe, PipeTransform } from '@angular/core';

@Pipe({name: "sortArray"})

export class SortArrayPipe implements PipeTransform {

  transform(array: Array<any>): Array<any> {

      return array.reverse();
  }
}
