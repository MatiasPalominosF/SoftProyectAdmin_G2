import { Component, OnInit, ViewChild, TemplateRef } from '@angular/core';
import { ProductService } from 'src/app/shared/services/product.service';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { FormGroup, FormControl, FormBuilder, Validators } from '@angular/forms';
import { UsuariosService } from 'src/app/_services/usuarios.service';
import { MatDialog, MatDialogConfig } from '@angular/material/dialog';
import { AddUserComponent } from '../add-user/add-user.component';
import { MatTableDataSource } from '@angular/material/table';
import { Users } from 'src/app/model-classes/users';
import { MatSort } from '@angular/material/sort';
import { MatPaginator } from '@angular/material/paginator';
import { ConfirmationDialogComponent } from '../../core/confirmation-dialog/confirmation-dialog.component';
import { EditUserComponent } from '../edit-user/edit-user.component';
import { MatSlideToggleChange } from '@angular/material/slide-toggle';
import { AuthenticationService } from 'src/app/_services/authentication.service';




@Component({
  selector: 'app-gestion',
  templateUrl: './gestion.component.html',
  styleUrls: ['./gestion.component.scss']
})
export class GestionComponent implements OnInit {
  usuarios: any = [];
  users: Users[];
  checked = true;
  isEnabled = true;
  userType: String;
  buttonDisabled: FormGroup;
  displayedColumns: string[] = ["run", "name", "surname", "email", "profile", "edit", "delete", "disable"];
  dataSource: MatTableDataSource<Users> = new MatTableDataSource<Users>();
  dialogResult = "";
  isDataLoading: boolean;
  loading: boolean;
  carga: any;
  fileToUpload: File = null;
  @ViewChild('infoImportModal') modalRef: TemplateRef<any>;

  addUserForm = this.fb.group({
    name: ['', Validators.required],
    surname: ['', Validators.required],
    email: ['', Validators.required],
    password: ['', Validators.required],
    rut: ['', Validators.required],
    profile: ['', Validators.required],
    enrollment: ['', Validators.required]
  });

  constructor(
    private modalService: NgbModal,
    private toastr: ToastrService,
    private usuariosService: UsuariosService,
    private dialog: MatDialog,
    private fb: FormBuilder,
    private auth: AuthenticationService,
  ) {
    this.getUsers();
  }

  getUsers() {
    this.loading = true;
    this.usuariosService.getAll().subscribe(
      data => {
        if (!data) {
          return;
        }
        this.users = data;
        this.dataSource.data = this.users;
        this.dataSource.sort = this.sort;
        this.dataSource.paginator = this.paginator;
        this.loading = false;
      });
  }

  @ViewChild(MatSort) sort: MatSort;
  @ViewChild(MatPaginator) paginator: MatPaginator;

  sortingCustomAccesor = (item, property) => {
    switch (property) {
      case 'run': return item.rut;
      case 'name': return item.name;
      case 'surname': return item.surname;
      case 'email': return item.email;
      case 'profile': return item.profile;
      default: return item[property];
    }
  };

  ngOnInit() {
    this.dataSource.sortingDataAccessor = this.sortingCustomAccesor;
    this.getUsers();
    console.log("userType:", this.auth.getUserType());
    this.userType = this.auth.getUserType();


    if (this.userType == "admin") {
      this.displayedColumns = ["run", "name", "surname", "email", "profile", "edit", "delete", "disable"];
    }
    else if (this.userType == "teacher") {
      this.displayedColumns = ["run", "name", "surname", "email", "profile"];
    }
  }

  public doFilter = (value: string) => {
    this.dataSource.filter = value.trim().toLocaleLowerCase();
  }


  ngAfterViewInit(): void {
    this.dataSource.paginator = this.paginator;
  }

  openAddDialog(): void {
    let dialogRef = this.dialog.open(AddUserComponent, {
      width: '600px',
      data: 'This text is passed into the dialog',
      disableClose: true,
      autoFocus: true
    });
    dialogRef.afterClosed().subscribe(result => {
      console.log(`Dialog closed: ${result}`);
      this.dialogResult = result;
      if (result == 'Confirm') {
        this.toastr.success('Usuario agregado exitosamente', 'Notificación', { timeOut: 3000 });
        this.refreshTable();
      }
    })
  }

  deleteUser(id: string) {
    this.openDeletionConfirmationDialog().afterClosed().subscribe(confirmation => {

      console.log(confirmation);
      if (confirmation.confirmed) {
        this.usuariosService.delete(id).subscribe({
          next: result => {
            console.log(result);
            this.refreshTable();
            this.toastr.success('Usuario eliminado correctamente', 'Notificación', { timeOut: 3000 });
          },
          error: result => {
            console.log(result);
          }
        });
      }
    });
  }

  editUser(id: string) {

    const dialogRef = this.dialog.open(EditUserComponent, {
      data: id,
      width: '600px',
      disableClose: true,
      autoFocus: true
    });

    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      if (result == 'Confirm') {
        this.refreshTable();
        this.toastr.success('Usuario editado exitosamente', 'Notificación', { timeOut: 3000 });
      }
    });

  }

  refreshTable() {
    this.getUsers();
  }

  openDeletionConfirmationDialog() {
    var deletionDialogConfig = this.getDialogConfig();
    deletionDialogConfig.data = { message: '¿Desea eliminar este usuario?' };
    return this.dialog.open(ConfirmationDialogComponent, deletionDialogConfig);
  }

  getDialogConfig() {
    const dialogConfig = new MatDialogConfig();

    dialogConfig.disableClose = true;
    dialogConfig.autoFocus = true;
    return dialogConfig;
  }

  addUser(modal, event) {
    event.target.parentElement.parentElement.blur();
    this.modalService.open(modal, { ariaLabelledBy: 'modal-basic-title', centered: true })
      .result.then((result) => {
        this.checked = true;
        var frm = this.addUserForm.value;
        this.usuariosService.insert(frm).subscribe((resp: any) => {
          if (resp.errors) {
            this.toastr.error('No se puede insertar el usuario en la base de datos.', 'Notificación de error', { timeOut: 3000 });
            return;
          }
          this.toastr.success('Usuario insertado correctamente', 'Notificación de inserción', { timeOut: 3000 });
        }, (error: any) => {
          console.log(error);
        });
      }, (reason) => {
      });
  }

  handleFileInput(files: FileList, modal) {
    this.fileToUpload = files.item(0);
    this.usuariosService.uploadFile(this.fileToUpload).subscribe((data: any) => {
      this.fileToUpload = null;
      if (!data.success) {
        this.toastr.error(data.msg, 'Notificación de error', { timeOut: 3000 });
        return;
      }
      this.toastr.success(data.msg, 'Notificación de exito', { timeOut: 3000 });
      this.carga = data;
      this.modalService.open(this.modalRef, { backdropClass: 'light-blue-backdrop' });
      this.getUsers();
    });
  }

  formatProfile(value) {
    switch (value) {
      case 'teacher':
        return 'Docente';
      case 'student':
        return 'Estudiante';
      case 'admin':
        return 'Administrador';
    }
  }

  //Método de prueba para setear un valor al switch.
  onChange(value) {
    if (value.checked == true) {
      this.isEnabled = true;
      console.log(this.isEnabled);
    } else {
      this.isEnabled = false;
      console.log(this.isEnabled);
    }
  }


  activeUserToggle(changeEvent: MatSlideToggleChange) {


    if (changeEvent.checked) {
      console.log(changeEvent.checked);
    } else {
      console.log(changeEvent.checked);
      this.dataSource.data = this.users;
      console.log("ESTOS DATILLOS EN USUARIO: ", this.dataSource.data);
    }
  }

}
