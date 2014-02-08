program BiblioTek;

uses
  jpeg,
  Forms,
  Unit1 in 'Unit1.pas' {frmMenuPrincipal},
  SolicitudesMaterial in 'SolicitudesMaterial.pas' {frmSolicitudes},
  uXPIcons in 'C:\MYVCL\uXPIcons\uXPIcons.pas',
  clases_app_biblio in 'clases_app_biblio.pas',
  FormBasica in 'FormBasica.pas' {frmFormaBasica},
  Datos in 'Datos.pas' {dmDatos: TDataModule},
  SeleccionaValores in 'SeleccionaValores.pas' {frmSeleccionaValores},
  SeleccionaCampo in 'SeleccionaCampo.pas' {frmSeleccionaCampo},
  SeleccionaSubCampo in 'SeleccionaSubCampo.pas' {frmSeleccionaSubCampo},
  ImportarTitulos in 'ImportarTitulos.pas' {frmImportarTitulos},
  Existencias in 'Existencias.pas' {frmExistencias};

{$R *.RES}

begin
  Application.Initialize;
  Application.Title := 'Sistema Integral de Gestión Bibliotecaria';
  Application.CreateForm(TfrmMenuPrincipal, frmMenuPrincipal);
  Application.CreateForm(TdmDatos, dmDatos);
  Application.CreateForm(TfrmSeleccionaValores, frmSeleccionaValores);
  Application.CreateForm(TfrmSeleccionaCampo, frmSeleccionaCampo);
  Application.CreateForm(TfrmSeleccionaSubCampo, frmSeleccionaSubCampo);

  if dmDatos.AbrirBdD then
     Application.Run;

end.
