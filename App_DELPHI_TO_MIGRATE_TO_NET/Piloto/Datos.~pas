(*******
  Historial de Cambios:

  MODULO DE DATOS

   04-feb-2009: Se crea el modulo, se decide utilizar un conector
                de datos ODBC (usando el componente mODBC)

 **)
unit Datos;

interface

uses
  SysUtils, Classes, mDataBas, mSession, DB, forms, windows, ADODB;

type

  TMyError = class(Exception)
  end;

  TdmDatos = class(TDataModule)
    mDataBase1: TADOConnection;
    qryAnySQL: TADOQuery;
  private
    { Private declarations }
  public
    { Public declarations }
    function AbrirBdD: boolean;
  end;

var
  dmDatos: TdmDatos;

implementation

{$R *.dfm}

function TdmDatos.AbrirBdD: boolean;
begin

   Result := false;

   try

      mDataBase1.Open; //.Connect;

      qryAnySQL.ConnectionString := dmDatos.mDataBase1.ConnectionString;

      Result := true;

   except

      MessageBox( 0, 'No se pudo gestionar la conexión con la base de datos BIBLIOTEK', 'No se pudo conectar al controlador ODBC', MB_OK );

      raise;

      //raise TMyError.Create('xx');
      //Application.MessageBox( 'No se pudo gestionar la conexión con la base de datos BIBLIOTEK', 'No se pudo conectar al controlador ODBC', 7 );

   end;

end;

end.
