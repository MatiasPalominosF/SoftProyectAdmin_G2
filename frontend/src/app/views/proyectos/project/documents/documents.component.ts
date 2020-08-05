import { Component, OnInit, Inject, Input } from '@angular/core';
import { FormGroup, FormControl, Validators, AbstractControl } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA } from '@angular/material/dialog';
import { UserRequirementService } from 'src/app/_services/userrequirements.service';
import { IncrementService } from 'src/app/_services/increments.service';
import { ViewChild } from '@angular/core'
import { NgIf } from '@angular/common';
import * as DecoupledEditor from '@ckeditor/ckeditor5-build-decoupled-document';
import { ChangeEvent } from '@ckeditor/ckeditor5-angular/ckeditor.component';
import {interval , timer } from 'rxjs';
import { ToastrService } from 'ngx-toastr';


@Component({
  selector: 'documents-component',
  templateUrl: './documents.component.html',
  styleUrls: ['./documents.component.scss']
})
export class DocumentsComponent implements OnInit {

  type;
  complete_document;
  simple_document;

  public Editor = DecoupledEditor;
  @ViewChild('editor') editorComponent: DecoupledEditor;
  documents_simplificado: any = [];
  documents_completo: any = [];

  anterior;
  actual;

  @Input() doctype: any;
  constructor
  (
    private toastr: ToastrService
  )
   {

    //Creamos el array con todos los documentos
    this.type = "complete";


    this.anterior = "1. Introduccion";
    this.actual = "1. Introduccion";

    this.documents_completo =
    [
    "1. Introduccion"
    ,
    "   1.1    Proposito del sistema",
    "   1.2   Alcance del proyecto",
    "   1.3   Contexto",
    "   1.4   Definiciones",
    "   1.5   Referencias",
    "2. Descripcion General"
    ,
    "   2.1   Suposiciones y Dependencias",
    "   2.2   Restriccion Generales",
    "   2.3   Usuarios",
    "   2.4   Producto",
    "   2.5   Ambiente",
    "   2.6   Proyectos relaciones"
    ,
    "3. Requisitos del Sistema",
    "   3.1   Requisitos de Usuario",
    "   3.2   Requisitos de Sistema",
    "4. Diseño"
    ,
    "   4.1   Arquitectura Fisica",
    "   4.2   Arquitectura Logica",
    "   4.3   Modelo de datos",
    "   4.4   Detalle de los Modulos",
    "   4.5   Navegacion",
    "   4.6   Diseño de diagrama de clases",
    "   4.7   Interfaz",
    "5. Modulos"
    ,
    "6. Implementacion del proyecto"
    ,
    "   6.1   Interfaces generales de los modulos implementados",
    "   6.2   Interfaces de las principales funciones implementadas"
    ,
    "7. Manual de instalacion de software"
    ,
    "   7.1   Requerimientos tecnicos",
    "   7.2   Instalcion paso a paso",
    "8. Casos de prueba"
    ,
    "9. Matriz de trazado"
    ,
    "   9.1   RU/RS",
    "   9.2   MD/RS",
    "   9.3   RU/CP",
    "   9.4   RS/CP",
    ];

    this.documents_simplificado =
    [
      "1. Introduccion"
      ,
      "   1.1 Proposito del sistema",
      "   1.2 Alcance del proyecto",
      "   1.3 Contexto",
      "   1.4 Referencias",
      "2. Descripcion General"
      ,
      "   2.1 Suposiciones y Dependencias",
      "   2.2 Restriccion Generales",
      "   2.3 Usuarios"
      ,
      "3. Requisitos de Usuario"
      ,
      "4. Diseño Arquitectonico"
      ,
      "   4.1 Arquitectura Fisica",
      "   4.2 Arquitectura Logica",
      "   4.3 Modelo de datos"
      ,
      "5. Diseño detallado"
      ,
      "   5.1 Diseño de diagrama de clases",
      "   5.2 Diseño de interfaces de usuario"
      ,
      "6. Implementacion del proyecto"
      ,
      "   6.1 Interfaces generales de los modulos implementados",
      "   6.2 Interfaces de las principales funciones implementadas"
      ,
      "7. Manual de instalacion de software"
      ,
      "   7.1 Requerimientos tecnicos",
      "   7.2 Instalcion paso a paso",
      "8. Casos de prueba"
      ,
      "9. Matriz de trazado"
      ,
      "   9.1 RU/CP",
      "   9.2 RS/CP",
      ];


  }

  /**
   *
   * @param e Cambiamos la seccion del documento
   */
  changed(e)
  {
    this.anterior = this.actual;
    this.actual = e;

    console.log("Anterior: " + this.anterior + "----- " + "Actual :"+ e );


  }


//**Guardamos el contenido del editor al hacer click */
public guardar()
{
  const data = this.getEditor().getData();
  console.log(this.type + "  "  + " data: " + data );
  this.toastr.success('Guardado', 'Guardando Documento', { timeOut: 3000, closeButton: true, progressBar: true });

}

public onReady( editor ) {
  editor.ui.getEditableElement().parentElement.insertBefore(
      editor.ui.view.toolbar.element,
      editor.ui.getEditableElement()
  );
}

ngOnInit(): void {
    if(this.doctype == 1){ this.simple_document=true;this.complete_document=false;}else{this.complete_document=true;this.simple_document=false;}
  const intervalo = interval(10000);
  intervalo.subscribe((n) =>
  {
    const data = this.getEditor().getData();

    console.log(this.type + "  "  + " data: " + data );
    console.log('Guardado Automatico - aki hay que guardar');
    this.toastr.success('Guardado Automatico', 'Guardando Documento', { timeOut: 3000, closeButton: true, progressBar: true });

  });

}

public getEditor() {
  // Warning: This may return "undefined" if the editor is hidden behind the `*ngIf` directive or
  // if the editor is not fully initialised yet.
  return this.editorComponent.editorInstance;
}

}
