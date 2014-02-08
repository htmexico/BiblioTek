(*******
  Historial de Cambios:

   22-ene-2009: Se crea el formato basico para estre proyecto.
   23-ene-2009:

 **)
unit FormBasica;

interface

uses
  Windows, Messages, SysUtils, Variants, Classes, Graphics, Controls, Forms,
  Dialogs, ExtCtrls, EditSpecial, StdCtrls, xpGroupBox, RoundButton,
  xpCheckBox, myform;

type

  TfrmFormaBasica = class(TFormSpecial)
    xpGroupBox1: TxpGroupBox;
    Label1: TLabel;
    EditSpecial1: TEditSpecial;
    Label2: TLabel;
    EditSpecial2: TEditSpecial;
    Label3: TLabel;
    Label4: TLabel;
    EditSpecial3: TEditSpecial;
    EditSpecial4: TEditSpecial;
    Label5: TLabel;
    EditSpecial5: TEditSpecial;
    Label6: TLabel;
    EditSpecial6: TEditSpecial;
    xpCheckBox1: TxpCheckBox;
    Encarta1: TEncarta;
    Encarta2: TEncarta;
    procedure Encarta1Click(Sender: TObject);
    procedure Encarta2Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
  end;

var
  frmFormaBasica: TfrmFormaBasica;

implementation

{$R *.dfm}

// fecha de creacion
// descripcion
// fecha de ult modificacion
procedure TfrmFormaBasica.FormCreate(Sender: TObject);
begin
   // iniciar todos los valores
   // que deben ser INICIADOS para evitar
   // conflictos
end;

// fecha de creacion
// descripcion
// fecha de ult modificacion
procedure TfrmFormaBasica.FormDestroy(Sender: TObject);
begin
   // Cerrar datasets
   // destruir objetos
   // destruir arreglos dinamicos

   //
   // 15feb2009: Se valida que el usuario no haya sido
   //   registrado previamente
   //if valida then
   //begin
   //end;

end;

procedure TfrmFormaBasica.Encarta1Click(Sender: TObject);
begin
   // Codigo para Guardar en Tabla
   // para guardar o hacer operaciones
   // de base de datos
   // se usaran elementos de tipo TQuery
   // PROHIBIDO: Utilizar TTable

   DisplayWarningMessage( 'Esta a punto de salir de la opcion SIN GUARDAR', 'Aviso' );

   Close;

end;

// fecha de creacion
// descripcion
// fecha de ult modificacion
procedure TfrmFormaBasica.Encarta2Click(Sender: TObject);
begin
   Free;
end;


end.
