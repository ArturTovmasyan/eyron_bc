import { async, inject, TestBed } from '@angular/core/testing';
import { RemoveTagPipe } from './removeTag.pipe';

fdescribe('Pipe: RemoveTagPipe', () => {
    let pipe;

    //setup
    beforeEach(async(() => {

            TestBed.configureTestingModule({
                providers: [ RemoveTagPipe ]
            })
                .compileComponents();
        }
    ));

    beforeEach(inject([RemoveTagPipe], p => {
        pipe = p;
    }));

    it('should work with empty string', () => {
        expect(pipe.transform('')).toEqual('');
    });

    it('should remove # tag', () => {
        expect(pipe.transform('#test')).toEqual('test');
    });

});