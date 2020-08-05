import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { environment } from '../../environments/environment';
import { Router } from '@angular/router';

@Injectable({ providedIn: 'root' })
export class CursosService {

    constructor(private http: HttpClient, private router: Router) { }

    getAll() {
        return this.http.get<any>(`${environment.apiUrl}/courses`);
    }

    get(id) {
        return this.http.get<any>(`${environment.apiUrl}/courses/` + id);
    }

    insert(data) {
        return this.http.post<any>(`${environment.apiUrl}/courses`, data);
    }

    delete(id) {
        return this.http.delete<any>(`${environment.apiUrl}/courses/` + id);
    }

    agregarProfeAcurso(data) {
        console.log("datos en service: ", data);
        return this.http.post<any>(`${environment.apiUrl}/courses/assign`, data);
    }

    agregarAyudanteAcurso(data) {
        return this.http.post<any>(`${environment.apiUrl}/courses/assignAssistant`, data);
    }

    insertComplete(data) {
        return this.http.post<any>(`${environment.apiUrl}/courses/createmass`, data);
    }

    usersList(id) {
        return this.http.get<any>(`${environment.apiUrl}/courses/list/` + id);
    }

    updateCourse(id, data) {
        return this.http.put<any>(`${environment.apiUrl}/courses/` + id, data);
    }

    uploadFile(fileToUpload: File, id): Observable<boolean> {
        const formData: FormData = new FormData();
        formData.append('file', fileToUpload, fileToUpload.name);

        return this.http.post<any>(`${environment.apiUrl}/courses/uploadfile/` + id, formData);
    }

}
