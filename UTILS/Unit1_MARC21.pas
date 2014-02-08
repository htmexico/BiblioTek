unit Unit1_MARC21;

interface

uses
  Windows, Messages, SysUtils, Classes, Graphics, Controls, Forms, Dialogs,
  myform, ComObj, StdCtrls, DBTables, Db, ComCtrls;

const

{ XlSheetType }
  xlChart = -4109;
  xlDialogSheet = -4116;
  xlExcel4IntlMacroSheet = 4;
  xlExcel4MacroSheet = 3;
  xlWorksheet = -4167;

{ XlWBATemplate }
  xlWBATChart = -4109;
  xlWBATExcel4IntlMacroSheet = 4;
  xlWBATExcel4MacroSheet = 3;
  xlWBATWorksheet = -4167;

type

  TForm1 = class(TFormSpecial)
    Label1: TLabel;
    Button1: TButton;
    ListBox1: TListBox;
    Button2: TButton;
    Database1: TDatabase;
    qryExecSQL: TQuery;
    qryVerifica: TQuery;
    Label2: TLabel;
    Button3: TButton;
    procedure Button1Click(Sender: TObject);
    procedure FormDestroy(Sender: TObject);
    procedure Button2Click(Sender: TObject);
    procedure FormCreate(Sender: TObject);
  private
    { Private declarations }
  public
    { Public declarations }
    Excel, oleDocument: OleVariant;
    XLApplication: Variant;

    SheetFormatos: Variant;

  end;

var
  Form1: TForm1;

implementation

uses Unit2_MARC21;

{$R *.DFM}

procedure TForm1.FormDestroy(Sender: TObject);
begin

  Database1.Close;

  if not VarIsEmpty(XLApplication) then
  begin
    XLApplication.DisplayAlerts := False;  // Discard unsaved files....
    XLApplication.WorkBooks.Close;
    XLApplication.Quit;
  end;

end;

procedure TForm1.Button1Click(Sender: TObject);
var
   i, j: integer;

begin
   // C:\BIBLIOTECA\biblio_doctos\MARC21
   ListBox1.Items.Clear;

   if not VarIsEmpty(XLApplication) then
   begin
     XLApplication.DisplayAlerts := False;  // Discard unsaved files....
     XLApplication.WorkBooks.Close;
     XLApplication.Quit;
   end;

   XLApplication := CreateOleObject('Excel.Application');
   XLApplication.Workbooks.Open ( 'C:\BIBLIOTECA\biblio_doctos\MARC21\DescripcionCampos_MARC_TraduccionJELS.xls' );

   for i := 1 to XLApplication.Workbooks.Count do
   begin
       for j := 1 to XLApplication.Workbooks[i].Sheets.Count do
       begin

           ListBox1.Items.Add( XLApplication.Workbooks[i].Sheets[j].Name );

       end;
   end;

end;


procedure TForm1.Button2Click(Sender: TObject);
var
   i, j, x: integer;
   nTodas: integer;

   nBook, nSheet: integer;

   cCampo: string;
   cCodigo, cIdentificador: string;
   cObsoleto: string;
   cDescripcionOriginal, cDescripcionEspanol: string;
   cValoresPosibles: string;
   cLigadoA: string;
   cLongitudFija: string;
   cUrl: string;

   cAutomatico: string;

   cPrimerChar: char;

   cNota: string;
   cConectorAACR: string;

   cIDValor, cRepetible: string;

   rowsEmtpy, nPos: integer;

   agregados, actualizados : integer;

   cTesauro1: string;
   cTesauro2: string;

   procedure AgregarActualizarValorPosible( nNivelMarc: integer; strCampo, strIdentificador, strcIDValor, cDescripcion, c_Nota: string );
   var
        cSubURL: string;
   begin

        // Viene en E
        cDescripcionEspanol := Trim(SheetFormatos.Range['E'+IntToStr(x)].Value);
        cSubURL             := Trim(SheetFormatos.Range['K'+IntToStr(x)].Value);

        if( Pos('[OBSOLETE]', cDescripcion ) <> 0 ) then
           cObsoleto := 'S';

        qryVerifica.SQL.Clear;
        qryVerifica.SQL.Add( 'SELECT COUNT(*) FROM marc_codigo21 ' );
        qryVerifica.SQL.Add( 'WHERE ID_CAMPO="'+strCampo+'" and NIVEL_MARC='+IntToStr(nNivelMarc)+' and CODIGO="'+strIdentificador+'" and SUBCODIGO="'+strcIDValor+'" ' );
        qryVerifica.Open;

        if qryVerifica.FieldByName('COUNT').AsInteger = 1 then
        begin
           qryExecSQL.SQL.Clear;
           qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
           qryExecSQL.SQL.Add( ' SET DESCRIPCION="'+cDescripcionEspanol+'", ' );
           qryExecSQL.SQL.Add( '     DESCRIPCION_ORIGINAL=:cDescripcionOriginal, ' );
           qryExecSQL.SQL.Add( '     LONGITUD=:cLongitud, URL=:cUrl, OBSOLETO="'+cObsoleto+'", ' );
           qryExecSQL.SQL.Add( '     NOTA="'+c_Nota+'" ' );
           qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+strCampo+'" and NIVEL_MARC='+IntToStr(nNivelMarc)+' and CODIGO="'+strIdentificador+'" and SUBCODIGO="'+strcIDValor+'" ' );
           qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcion;
           qryExecSQL.ParamByName('cLongitud').AsString  := cLongitudFija;
           qryExecSQL.ParamByName('cURL').AsString       := cSubURL;

           try
              qryExecSQL.ExecSQL;
           except
              Self.DisplayWarningMessage( 'Error', 'Error' );
           end;

           actualizados := actualizados + 1;
        end
        else
        begin
           qryExecSQL.SQL.Clear;
           qryExecSQL.SQL.Add( 'INSERT INTO marc_codigo21 (ID_CAMPO, NIVEL_MARC, CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, NOTA, OBSOLETO, LONGITUD, URL ) ' );
           qryExecSQL.SQL.Add( 'VALUES ("'+strCampo+'", '+IntToStr(nNivelMarc)+', "'+strIdentificador+'", "'+strcIDValor+'", ' );
           qryExecSQL.SQL.Add( ' "'+cDescripcionEspanol+'", ' );
           qryExecSQL.SQL.Add( ' "'+cDescripcion+'", ' );
           qryExecSQL.SQL.Add( ' "'+c_Nota+'", ' );
           qryExecSQL.SQL.Add( ' "'+cObsoleto+'", "'+cLongitudFija+'", "'+cSubURL+'" ) ' );

           try
              qryExecSQL.ExecSQL;
           except
              Self.DisplayWarningMessage( 'Error '+strCampo+' '+strIdentificador, 'Error' );
           end;

           agregados := agregados + 1;
        end;

        qryVerifica.Close;

   end;

   function Agregar_R_NR_Obsolete( DescripOriginal, DescripEspanol: string; var cRep: string ): string;
   begin

        Result := DescripEspanol;
        cRep   := '';

        if DescripEspanol <> '' then
        begin
           if (Pos('(R)', DescripOriginal)<>0) and (DescripEspanol<>'') then
           begin
              if Pos('(R)', DescripEspanol ) = 0 then
              begin
                 DescripEspanol := DescripEspanol + ' (R)';
                 cRep     := 'S';
              end;
           end;

           if (Pos('(NR)', DescripOriginal)<>0) and (DescripEspanol<>'') then
           begin
              if Pos('(NR)', DescripEspanol ) = 0 then
                 DescripEspanol := DescripEspanol + ' (NR)';
           end;

           if (Pos('[OBSOLETE]', DescripOriginal)<>0) and (DescripEspanol<>'') then
           begin
              if Pos('(OBSOLETO)', DescripEspanol ) = 0 then
                 DescripEspanol := DescripEspanol + ' (OBSOLETO)';
           end;

           Result := DescripEspanol;
        end;

   end;

   //
   // CABECERA DE DEFINICION DE CAMPO
   // NIVEL_MARC = 1
   //
   procedure AgregarActualizarCampo_Nivel_1;
   begin

      cAutomatico := SheetFormatos.Range['H'+IntToStr(x)].Value;
      cAutomatico := Trim(cAutomatico);

      cDescripcionEspanol := SheetFormatos.Range['D'+IntToStr(x)].Value;

      cRepetible := '';

      if( Pos('[OBSOLETE]', cDescripcionOriginal ) <> 0 ) then
          cObsoleto := 'S';

      //if( Pos('[OBSOLETE]', cValoresPosibles ) <> 0 ) then
      //    cObsoleto := 'S';

      cDescripcionEspanol := Agregar_R_NR_OBSOLETE( cDescripcionOriginal, cDescripcionEspanol, cRepetible );

      cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;

      qryVerifica.SQL.Clear;
      qryVerifica.SQL.Add( 'SELECT COUNT(*) FROM marc_codigo21 ' );
      qryVerifica.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=1' );
      qryVerifica.Open;

      if qryVerifica.FieldByName('COUNT').AsInteger = 1 then
      begin
         qryExecSQL.SQL.Clear;
         qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
         qryExecSQL.SQL.Add( ' SET DESCRIPCION="'+cDescripcionEspanol+'", ' );
         qryExecSQL.SQL.Add( '     DESCRIPCION_ORIGINAL=:cDescripcionOriginal, ' );
         qryExecSQL.SQL.Add( '     AUTOMATICO="'+cAutomatico+'", ' );
         qryExecSQL.SQL.Add( '     NOTA="'+cNota+'", ' );
         qryExecSQL.SQL.Add( '     LONGITUD=:cLongitud, URL=:cUrl, OBSOLETO="'+cObsoleto+'", REPETIBLE="'+cRepetible+'" ' );
         qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=1 and CODIGO="" and SUBCODIGO=""' );
         qryExecSQL.ParamByName('cLongitud').AsString := cLongitudFija;
         qryExecSQL.ParamByName('cURL').AsString       := cUrl;
         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;

         try
            qryExecSQL.ExecSQL;
         except
            Self.DisplayWarningMessage( 'Error'+cDescripcionOriginal, 'Error' );
         end;

         actualizados := actualizados + 1;
      end
      else
      begin
         qryExecSQL.SQL.Clear;
         qryExecSQL.SQL.Add( 'INSERT INTO marc_codigo21 (ID_CAMPO, NIVEL_MARC, AUTOMATICO, CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, NOTA, OBSOLETO, LONGITUD, URL, REPETIBLE ) ' );
         qryExecSQL.SQL.Add( 'VALUES ("'+cCampo+'", 1, "'+cAutomatico+'", "", "", ' );
         qryExecSQL.SQL.Add( ' "'+cDescripcionEspanol+'", ' );
         qryExecSQL.SQL.Add( ' "'+cDescripcionOriginal+'", ' );
         qryExecSQL.SQL.Add( ' "'+cNota+'", ' );
         qryExecSQL.SQL.Add( ' "'+cObsoleto+'", "'+cLongitudFija+'", "'+cUrl+'", "'+cRepetible+'" ) ' );

         try
            qryExecSQL.ExecSQL;
         except
            raise;
            Self.DisplayWarningMessage( 'Error '+cDescripcionOriginal, 'Error' );
         end;

         agregados := agregados + 1;
      end;

      qryVerifica.Close;

   end;

begin

   if ListBox1.ItemIndex = -1 then
   begin
      Self.DisplayStopMessage( 'Por favor selecciona una hoja', 'Alto' );
      exit;
   end;

   nBook  := -1;
   nSheet := 0;

   for i := 1 to XLApplication.Workbooks.Count do
   begin
       nTodas := XLApplication.Workbooks[i].Sheets.Count;

       for j := 1 to nTodas do
       begin

           if Trim(XLApplication.Workbooks[i].Sheets[j].Name) = ListBox1.Items[ ListBox1.ItemIndex ] Then
           begin

              nBook := i;
              nSheet := j;

              break;
           end;

       end;
   end;

   Label2.Caption := '';

   If ( nBook <> -1 ) then
   begin
      SheetFormatos := XLApplication.Workbooks[ nBook ].Sheets[ nSheet ];

      rowsEmtpy := 0;

      agregados := 0;
      actualizados := 0;

      for x := 3 to 4500 do
      begin

          Label2.Caption := 'Renglon: '+IntToStr(x);

          if cCampo = '' then rowsEmtpy := rowsEmtpy + 1
          else rowsEmtpy := 0;

          if( rowsEmtpy >= 50 ) then
              break;

          cCampo := '';

          cCampo := SheetFormatos.Range['A'+IntToStr(x)].Value;

          cObsoleto := '';
          cDescripcionOriginal := '';
          cDescripcionEspanol  := '';
          cValoresPosibles     := '';
          cLigadoA             := '';
          cLongitudFija        := '';
          cUrl                 := '';

          cNota                := '';  // 15-dic-2008

          if cCampo <> '' then
          begin
             cDescripcionOriginal := Trim(SheetFormatos.Range['B'+IntToStr(x)].Value);
             cValoresPosibles     := Trim(SheetFormatos.Range['D'+IntToStr(x)].Value);
             cLigadoA             := Trim(SheetFormatos.Range['F'+IntToStr(x)].Value);
             cLongitudFija        := Trim(SheetFormatos.Range['G'+IntToStr(x)].Value);
             cUrl                 := Trim(SheetFormatos.Range['J'+IntToStr(x)].Value);


             if cUrl <> '' then
             begin

                if cUrl = '???' then
                   cUrl := '';

                //
                // CABECERA DE DEFINICION DE CAMPO
                // NIVEL_MARC = 1
                //

                AgregarActualizarCampo_Nivel_1;

             end
             else
             begin
                // OTROS CAMPOS Y DETALLE ADICIONAL

                cPrimerChar := #0;

                if cDescripcionOriginal <> '' then
                   cPrimerChar := cDescripcionOriginal[1];

                if Copy(cDescripcionOriginal,1,1) = '{' then
                begin
                   // comentario o descripcion
                   // QUITADA PORQUE SE ANEXA UNA URL PARA CONSULTAR EL ORIGINAL EN INGL�S en la definicion del campo
                end
                else if cPrimerChar in ['0'..'9'] then
                begin
                   //
                   // codigos de posicion
                   //
                   nPos := Pos( '-', cDescripcionOriginal );

                   if nPos = 0 then
                      if (Length(cDescripcionOriginal)>4) and (nPos=0) then
                         nPos := Pos( ' ', cDescripcionOriginal );

                   if nPos = 0 then
                   begin
                      if ((Length(cDescripcionOriginal)=2) or (Length(cDescripcionOriginal)=6)) and (nPos=0) then
                      begin
                         //
                         // VALOR POSIBLE DE LLENADO DE POSICIONES PARA CAMPOS DE ANCHO FIJO
                         // NIVEL_MARC=3
                         //

                         nPos := Pos( '-', cValoresPosibles );

                         if nPos <> 0 then
                         begin
                            cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                            cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                            cIdentificador := Trim(cDescripcionOriginal);

                            If copy(cIdentificador,1,1)='%' then
                               cIdentificador := copy(cIdentificador,2,255);

                            If copy(cIdentificador,Length(cIdentificador),1)='%' then
                              cIdentificador := copy(cIdentificador,1,Length(cIdentificador)-1);

                            // Nota est� en I
                            cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                            cNota := Trim(cNota);

                            if cLigadoA <> '' then
                               cIDValor := cLigadoA + '?' + cIDValor;

                            AgregarActualizarValorPosible( 3, cCampo, cIdentificador, cIDValor, cValoresPosibles, cNota );
                         end
                         else
                            DisplayWarningMessage( 'Error en valor de identificador de campo: '+cCampo+' '+cDescripcionOriginal, 'Error' )

                      end
                      else
                         DisplayWarningMessage( 'Error en identificador de campo por posici�n: '+cCampo+' '+cDescripcionOriginal, 'Error' )
                   end
                   else
                   begin
                      //
                      // UN ENCABEZADO DE LLENADO DE POSICION TAMBIEN TRAE UN VALOR POSIBLE
                      // NIVEL_MARC = 2
                      //
                      cIdentificador := Trim(Copy( cDescripcionOriginal, 1, nPos-1 ));

                      cDescripcionOriginal := Trim(Copy( cDescripcionOriginal, nPos+1, 255 ));

                      // est� en C
                      cDescripcionEspanol := SheetFormatos.Range['C'+IntToStr(x)].Value;

                      if Pos('[OBSOLETE]', cDescripcionOriginal ) <> 0 then
                          cObsoleto := 'S';

                      cDescripcionEspanol := Agregar_R_NR_OBSOLETE( cDescripcionOriginal, cDescripcionEspanol, cRepetible );

                      //if (cCampo = '008') and (Pos('m',cLigadoA ) <> 0) then
                      //   cLigadoA := '' + cLigadoA;
                      qryVerifica.SQL.Clear;
                      qryVerifica.SQL.Add( 'SELECT COUNT(*) FROM marc_codigo21 ' );
                      qryVerifica.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=2 and CODIGO="'+cIdentificador+'" and SUBCODIGO="'+cLigadoA+'" ' );
                      qryVerifica.Open;

                      if qryVerifica.FieldByName('COUNT').AsInteger = 1 then
                      begin
                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
                         qryExecSQL.SQL.Add( ' SET DESCRIPCION="'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( '     DESCRIPCION_ORIGINAL=:cDescripcionOriginal, ' );
                         qryExecSQL.SQL.Add( '     LONGITUD=:cLongitud, URL=:cUrl, OBSOLETO="'+cObsoleto+'", REPETIBLE="'+cRepetible+'" ' );
                         qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=2 and CODIGO="'+cIdentificador+'" and SUBCODIGO="'+cLigadoA+'" ' );
                         qryExecSQL.ParamByName('cLongitud').AsString := cLongitudFija;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;

                         try
                            qryExecSQL.ExecSQL;
                         except
                            Self.DisplayWarningMessage( 'Error >', 'Error' );
                         end;

                         actualizados := actualizados + 1;
                      end
                      else
                      begin
                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'INSERT INTO marc_codigo21 (ID_CAMPO, NIVEL_MARC, CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO, LONGITUD, URL, REPETIBLE ) ' );
                         qryExecSQL.SQL.Add( 'VALUES (:cIDCampo, 2, "'+cIdentificador+'", "'+cLigadoA+'", ' );
                         qryExecSQL.SQL.Add( ' "'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( ' :cDescripcionOriginal, ' );
                         qryExecSQL.SQL.Add( ' "'+cObsoleto+'", :cLongitud, :cUrl, :cRepetible ) ' );
                         qryExecSQL.ParamByName('cIDCampo').asString   := cCampo;
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;
                         qryExecSQL.ParamByName('cLongitud').AsString  := cLongitudFija;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ParamByName('cRepetible').AsString           := cRepetible;

                         try
                            qryExecSQL.ExecSQL;
                         except
                            Self.DisplayWarningMessage( 'Error', 'Error' );
                         end;

                         agregados := agregados + 1;
                      end;

                      qryVerifica.Close;

                      // AHORA AGREGAR EL VALOR POSIBLE
                      // NIVEL_MARC = 3
                      nPos := Pos( '-', cValoresPosibles );

                      if nPos <> 0 then
                      begin
                         cLigadoA := Trim(SheetFormatos.Range['F'+IntToStr(x)].Value);

                         cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                         cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                         // Nota est� en I
                         cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                         cNota := Trim(cNota);

                         if cLigadoA <> '' then
                            cIDValor := cLigadoA + '?' + cIDValor;

                         AgregarActualizarValorPosible( 3, cCampo, cIdentificador, cIDValor, cValoresPosibles, cNota );
                      end;

                   end;

                end
                else if Pos( '%X', cDescripcionOriginal ) <> 0 then
                begin
                   //
                   // Identificadores
                   //
                   nPos := Pos( '-', cDescripcionOriginal );

                   if nPos = 0 then
                   begin
                      if (Length(cDescripcionOriginal)>4) and (nPos=0) then
                         nPos := Pos( ' ', cDescripcionOriginal );
                   end;

                   if nPos = 0 then
                   begin
                      if (Length(cDescripcionOriginal)=4) and (nPos=0) then
                      begin
                         //
                         // VALOR POSIBLE DE IDENTIFICADOR
                         // NIVEL_MARC=6

                         nPos := Pos( '-', cValoresPosibles );

                         if nPos <> 0 then
                         begin
                            cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                            cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                            cIdentificador := Trim(cDescripcionOriginal);

                            If copy(cIdentificador,1,1)='%' then
                               cIdentificador := copy(cIdentificador,2,255);

                            If copy(cIdentificador,Length(cIdentificador),1)='%' then
                              cIdentificador := copy(cIdentificador,1,Length(cIdentificador)-1);

                            // Nota est� en I
                            cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                            cNota := Trim(cNota);

                            AgregarActualizarValorPosible( 6, cCampo, cIdentificador, cIDValor, cValoresPosibles, cNota );
                         end
                         else
                            DisplayWarningMessage( 'Error en valor de identificador de campo: '+cCampo+' '+cDescripcionOriginal, 'Error' )

                      end
                      else
                         DisplayWarningMessage( 'Error en identificador de campo: '+cCampo, 'Error' )
                   end
                   else
                   begin
                      //
                      // UN ENCABEZADO DE IDENTIFICADOR TAMBIEN TRAE UN VALOR POSIBLE
                      // NIVEL_MARC = 5
                      //
                      cIdentificador := Trim(Copy( cDescripcionOriginal, 1, nPos-1 ));

                      If copy(cIdentificador,1,1)='%' then
                         cIdentificador := copy(cIdentificador,2,255);

                      If copy(cIdentificador,Length(cIdentificador),1)='%' then
                         cIdentificador := copy(cIdentificador,1,Length(cIdentificador)-1);

                      cDescripcionOriginal := Trim(Copy( cDescripcionOriginal, nPos+1, 255 ));

                      // est� en C
                      cDescripcionEspanol := SheetFormatos.Range['C'+IntToStr(x)].Value;

                      if( Pos('[OBSOLETE]', cDescripcionOriginal ) <> 0 ) then
                          cObsoleto := 'S';

                      cDescripcionEspanol := Agregar_R_NR_OBSOLETE( cDescripcionOriginal, cDescripcionEspanol, cRepetible );

                      qryVerifica.SQL.Clear;
                      qryVerifica.SQL.Add( 'SELECT COUNT(*) FROM marc_codigo21 ' );
                      qryVerifica.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=5 and CODIGO="'+cIdentificador+'" and SUBCODIGO="" ' );
                      qryVerifica.Open;

                      if qryVerifica.FieldByName('COUNT').AsInteger = 1 then
                      begin
                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
                         qryExecSQL.SQL.Add( ' SET DESCRIPCION="'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( '     DESCRIPCION_ORIGINAL=:cDescripcionOriginal, ' );
                         qryExecSQL.SQL.Add( '     LONGITUD=:cLongitud, URL=:cUrl, OBSOLETO="'+cObsoleto+'", REPETIBLE="'+cRepetible+'" ' );
                         qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=5 and CODIGO="'+cIdentificador+'" and SUBCODIGO="" ' );
                         qryExecSQL.ParamByName('cLongitud').AsString := cLongitudFija;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;

                         try
                            qryExecSQL.ExecSQL;
                         except
                            Self.DisplayWarningMessage( 'Error', 'Error' );
                         end;

                         actualizados := actualizados + 1;
                      end
                      else
                      begin
                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'INSERT INTO marc_codigo21 (ID_CAMPO, NIVEL_MARC, CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, OBSOLETO, LONGITUD, URL, REPETIBLE ) ' );
                         qryExecSQL.SQL.Add( 'VALUES (:cIDCampo, 5, "'+cIdentificador+'", "", ' );
                         qryExecSQL.SQL.Add( ' "'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( ' :cDescripcionOriginal, ' );
                         qryExecSQL.SQL.Add( ' "'+cObsoleto+'", :cLongitud, :cUrl, :cRepetible ) ' );
                         qryExecSQL.ParamByName('cIDCampo').asString   := cCampo;
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;
                         qryExecSQL.ParamByName('cLongitud').AsString  := cLongitudFija;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ParamByName('cRepetible').AsString           := cRepetible;

                         try
                            qryExecSQL.ExecSQL;
                         except
                            Self.DisplayWarningMessage( 'Error', 'Error' );
                         end;

                         agregados := agregados + 1;
                      end;

                      qryVerifica.Close;

                      // AHORA AGREGAR EL VALOR POSIBLE
                      // NIVEL_MARC = 6
                      nPos := Pos( '-', cValoresPosibles );

                      if nPos <> 0 then
                      begin
                         cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                         cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                         // Nota est� en I
                         cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                         cNota := Trim(cNota);

                         AgregarActualizarValorPosible( 6, cCampo, cIdentificador, cIDValor, cValoresPosibles, cNota );
                      end;

                   end;

                end
                else if Pos( '$', cDescripcionOriginal ) <> 0 then
                begin
                   //
                   // SubCampos
                   // NIVEL_MARC = 9 y 10
                   //
                   //
                   nPos := Pos( '-', cDescripcionOriginal );

                   if nPos = 0 then
                   begin

                      // Buscar si es un valor posible
                      if Length(cDescripcionOriginal)=2 then
                      begin
                         // Es un valor posible de subcampo
                         //
                         // VALOR POSIBLE DE SUBCAMPO
                         // NIVEL_MARC=10
                         //

                         nPos := Pos( '- ', cValoresPosibles );

                         if nPos <> 0 then
                         begin
                            cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                            cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                            cCodigo := Trim(cDescripcionOriginal);

                            // Nota est� en I
                            cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                            cNota := Trim(cNota);

                            AgregarActualizarValorPosible( 10, cCampo, cCodigo, cIDValor, cValoresPosibles, '' );

                            // Colocar el tag {MARC}
                            // en el campo TESAURO de la definici�n del subcampo NIVEL_MARC=9
                            qryExecSQL.SQL.Clear;
                            qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
                            qryExecSQL.SQL.Add( ' SET TESAURO="{MARC}" ' );
                            qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=9 and CODIGO="'+cCodigo+'" and SUBCODIGO="" ' );
                            qryExecSQL.ExecSQL;

                         end
                         else
                            DisplayWarningMessage( 'Error en valor de identificador de campo: '+cCampo+' '+cDescripcionOriginal, 'Error' );

                      end
                      else
                          Self.DisplayWarningMessage( 'Error en subcampo '+cCampo+' '+cDescripcionOriginal, 'Error' );

                   end
                   else
                   begin
                      cCodigo := Trim(Copy( cDescripcionOriginal, 1, nPos-1 ));

                      cDescripcionOriginal := Trim(Copy( cDescripcionOriginal, nPos+1, 255 ));

                      // en C
                      cDescripcionEspanol := SheetFormatos.Range['C'+IntToStr(x)].Value;

                      if( Pos('[OBSOLETE]', cDescripcionOriginal ) <> 0 ) then
                          cObsoleto := 'S';

                      cDescripcionEspanol := Agregar_R_NR_OBSOLETE(cDescripcionOriginal, cDescripcionEspanol, cRepetible );

                      // Nota est� en I
                      cNota := SheetFormatos.Range['I'+IntToStr(x)].Value;
                      cNota := Trim(cNota);

                      cConectorAACR := '';
                      cConectorAACR := SheetFormatos.Range['N'+IntToStr(x)].Value;

                      if cConectorAACR <> '' then
                         cConectorAACR := Trim(cConectorAACR);

                      qryVerifica.SQL.Clear;
                      qryVerifica.SQL.Add( 'SELECT COUNT(*) FROM marc_codigo21 ' );
                      qryVerifica.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=9 and CODIGO="'+cCodigo+'" and SUBCODIGO=""' );
                      qryVerifica.Open;

                      if qryVerifica.FieldByName('COUNT').AsInteger = 1 then
                      begin

                         cTesauro1 := '';

                         if Pos('{', cValoresPosibles ) <> 0 then
                            cTesauro1 := ' TESAURO="'+cValoresPosibles+'",';

                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
                         qryExecSQL.SQL.Add( ' SET DESCRIPCION="'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( '     DESCRIPCION_ORIGINAL=:cDescripcionOriginal, ' + cTesauro1 );
                         qryExecSQL.SQL.Add( '     NOTA="'+cNota+'", ' );
                         qryExecSQL.SQL.Add( '     LONGITUD=:cLongitud, URL=:cUrl, OBSOLETO="'+cObsoleto+'", REPETIBLE="'+cRepetible+'", ' );
                         qryExecSQL.SQL.Add( '     CONECTOR_AACR="'+cConectorAACR+'" ' );
                         qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=9 and CODIGO="'+cCodigo+'" and SUBCODIGO="" ' );
                         qryExecSQL.ParamByName('cLongitud').AsString := cLongitudFija;
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ExecSQL;

                         actualizados := actualizados + 1;
                      end
                      else
                      begin

                         cTesauro1 := '';

                         if Pos('{', cValoresPosibles ) <> 0 then
                         begin
                            cTesauro1 := ', TESAURO ';
                            cTesauro2 := ', "'+cValoresPosibles+'" ';
                         end;

                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'INSERT INTO marc_codigo21 (ID_CAMPO, NIVEL_MARC, CODIGO, SUBCODIGO, DESCRIPCION, DESCRIPCION_ORIGINAL, NOTA, CONECTOR_AACR, OBSOLETO, LONGITUD, URL, REPETIBLE '+cTesauro1+' ) ' );
                         qryExecSQL.SQL.Add( 'VALUES ("'+cCampo+'", 9, "'+cCodigo+'", "", ' );
                         qryExecSQL.SQL.Add( ' "'+cDescripcionEspanol+'", ' );
                         qryExecSQL.SQL.Add( ' :cDescripcionOriginal, ' );
                         qryExecSQL.SQL.Add( ' "'+cNota+'", ' );
                         qryExecSQL.SQL.Add( ' "'+cConectorAACR+'", ' );
                         qryExecSQL.SQL.Add( ' "'+cObsoleto+'", "'+cLongitudFija+'", :cUrl, "'+cRepetible+'"'+cTesauro2+' ) ' );
                         qryExecSQL.ParamByName('cURL').AsString       := cUrl;
                         qryExecSQL.ParamByName('cDescripcionOriginal').AsString := cDescripcionOriginal;
                         qryExecSQL.ExecSQL;

                         agregados := agregados + 1;
                      end;

                      qryVerifica.Close;

                      // AHORA AGREGAR EL VALOR POSIBLE PARA SUBCAMPO
                      // NIVEL_MARC = 10
                      nPos := Pos( '- ', cValoresPosibles );

                      if (nPos <> 0) and (nPos<5) then
                      begin
                         cIDValor := Trim(Copy( cValoresPosibles, 1, nPos-1 ));
                         cValoresPosibles := Trim(Copy( cValoresPosibles, nPos+1, 255 ));

                         cNota := '';

                         AgregarActualizarValorPosible( 10, cCampo, cCodigo, cIDValor, cValoresPosibles, cNota );

                         // Colocar el tag {MARC}
                         // en el campo TESAURO de la definici�n del subcampo NIVEL_MARC=9
                         qryExecSQL.SQL.Clear;
                         qryExecSQL.SQL.Add( 'UPDATE marc_codigo21 ' );
                         qryExecSQL.SQL.Add( ' SET TESAURO="{MARC}" ' );
                         qryExecSQL.SQL.Add( 'WHERE ID_CAMPO="'+cCampo+'" and NIVEL_MARC=9 and CODIGO="'+cCodigo+'" and SUBCODIGO="" ' );
                         qryExecSQL.ExecSQL;

                      end;

                   end;
                end;

             end;

          end;

          Application.ProcessMessages;

      end;
      {for}

      Self.DisplayInfoMessage( 'Se actualizaron '+IntToStr(actualizados)+#13#13+
                               'Se agregaron '+IntToStr(agregados)+#13#13, 'Aviso' )
   end;

end;

procedure TForm1.FormCreate(Sender: TObject);
begin

   Label2.Caption := '';
   
   Database1.Open;

end;


end.
