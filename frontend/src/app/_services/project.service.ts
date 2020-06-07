import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from "@angular/common/http";
import { Observable } from 'rxjs';
import { Project } from '../model-classes/project';
import { map } from "rxjs/operators";
import { environment as env } from '../../environments/environment';
//import { environment as env } from "envi";

@Injectable({
  providedIn: 'root'
})
export class ProjectService {

  constructor(private http: HttpClient) { }

  addProject(data: Project): Observable<boolean> {
    const body = new HttpParams()
      .set("name", data.name)
      .set("description", data.description);

    return this.http.post<any>(`${env.apiUrl}/project/addProject`, body)
      .pipe(map(result => {
        console.log(result.msg);
        return true;
      }));
  }

  getAllProjects(): Observable<Project[]> {
    return this.http.get<any>(`${env.apiUrl}/project/getAll`)
      .pipe(map(result => {
        console.log(result.msg);
        return result;
      }));
  }

}
