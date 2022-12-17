<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="author" content="Luis Manuel Gonzále Baizán" />
    <meta name="viewport" content ="width=device-width, initial scale=1.0" />
    <title>Ejercicio-1</title>
    <link rel="stylesheet" href="CalculadoraMilan.css">
</head>
<body>
    <h1>Calculadora Milan</h1>

    <?php
        session_start(); // iniciamos SESSION

        if (!isset($_SESSION['sesion_pantalla']))
            $_SESSION['sesion_pantalla'] = '';

        // Manejamos la memoria a través de la sesión
        if (!isset($_SESSION['sesion_memoria']))
            $_SESSION['sesion_memoria'] = 0;

        class CalculadoraMilan {
            // Manejamos la pantalla
            private $pantalla; // valor que debe mostrarse en la pantalla de la calculadora
            private $operacion;
            private $memoria;  

            public function __construct () {
                $this->pantalla = '';
                $this->operacion = '';
                $this->memoria = 0;

                if (count($_POST) > 0) {
                    // Nos encargamos de manejar los botones de los números
                    if(isset($_POST['1'])) $this->digito(1);
                    if(isset($_POST['2'])) $this->digito(2);
                    if(isset($_POST['3'])) $this->digito(3);
                    if(isset($_POST['4'])) $this->digito(4);
                    if(isset($_POST['5'])) $this->digito(5);
                    if(isset($_POST['6'])) $this->digito(6);
                    if(isset($_POST['7'])) $this->digito(7);
                    if(isset($_POST['8'])) $this->digito(8);
                    if(isset($_POST['9'])) $this->digito(9);
                    if(isset($_POST['0'])) $this->digito(0);

                    // Nos encargamos de manejar las operaciones
                    if(isset($_POST['division'])) $this->division();
                    if(isset($_POST['raiz'])) $this->unary_operation(fn($x) => sqrt($x));
                    if(isset($_POST['masMenos'])) $this->unary_operation(fn($x) => $x * (-1));
                    if(isset($_POST['producto'])) $this->multiplicacion();
                    if(isset($_POST['modulo'])) $this->modulo();
                    if(isset($_POST['resta'])) $this->resta();
                    if(isset($_POST['suma'])) $this->suma();
                    if(isset($_POST['punto'])) $this->punto();
                    if(isset($_POST['igual'])) $this->igual();

                    // Nos encargamos de manejar la memoria
                    if(isset($_POST['mrc'])) $this->mrc();
                    if(isset($_POST['m-'])) $this->m_menos();
                    if(isset($_POST['m+'])) $this->m_mas();

                    // Otros botones
                    if(isset($_POST['borrar'])) $this->borrar();
                    if(isset($_POST['borrarUltimoDig'])) $this->borrarUltimoDig();

                    // Por si acabamos de hacer unset a las sesiones

                    if (!isset($_SESSION['sesion_pantalla']))
                        $_SESSION['sesion_pantalla'] = '';
            
                    // Manejamos la memoria a través de la sesión
                    if (!isset($_SESSION['sesion_memoria']))
                        $_SESSION['sesion_memoria'] = 0;

                    $_SESSION['sesion_pantalla'] .= $this->pantalla;
                }
            }
        
            public function digito($numero) {
                //$this->compruebaOperador();
                $this->pantalla .= $numero;
                //$this->operacion .= $numero;
            }

            public function punto() {
                $this->pantalla .= ".";
            }

            public function suma() {
                //if($this->operacion!="") {
                //    $this->igual();
                //    $this->operacion = '';
                //}
                //$this->compruebaOperador();
                $this->pantalla .= "+";
                //$this->operacion = "+";
            }

            public function resta() {
                $this->pantalla .= "-";
                //$this->operacion .= "-";
            }

            public function modulo() {
                $this->pantalla .= "%";
            }

            public function multiplicacion() {
                $this->pantalla .= "*";
            }

            public function division() {
                $this->pantalla .= "/";
            }

            /*public function compruebaOperador() {
                if ($this->hayOperador()) {
                    $this->pantalla = substr($this->pantalla, 0, -1);
                    $this->operacion = substr($this->operacion, 0, -1);
                }
        
                if ($this->pantalla == "") {
                    $this->pantalla = 0;
                    $this->operacion = 0;
                }
            }
        
            public function hayOperador() {
                $caracter = substr($this->pantalla, -1);
                return ($caracter == "+" || $caracter == "-" || $caracter == "*"
                    || $caracter == "/" || $caracter == ".");
            }*/

            // Añadimos el caracter a la pantalla
            public function caracter($caracter) {
                $this->pantalla .= $caracter;
            }
        
            /** Igual: evalua los operandos y operador que hemos indicado. Y maneja las 
             *  excepciones que puedan surgir:
             *      A) Si no hemos indicado bien algún operando --> ERROR
             *      B) Si no hemos indicado bien algún operador --> ERROR
             *      C) Si falla la evaluación --> ERROR
             * 
             *  --> NOTA: si quieres seguir trabajando con ese valor computado, deberás
             *  utilizar las teclas de memoria (para eso están).
             */
            private function igual() {
                if (isset($_SESSION['sesion_pantalla']))
                    try {
                        $expresion = $_SESSION['sesion_pantalla'];
                        $_SESSION['sesion_pantalla'] = eval("return $expresion ;"); 
                    } catch (Exception $e) {
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    } catch(ParseError $p){
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    } catch(DivisionByZeroError $d){
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    } catch(Error $e){
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    }
            }
        
            // C: Reestablece la calculadora a un estado inicial.
            private function borrar() {
                unset($_SESSION['sesion_pantalla']);
                unset($_SESSION['sesion_memoria']);
            }

            private function borrarUltimoDig() {
                $_SESSION['sesion_pantalla'] = substr($_SESSION['sesion_pantalla'],
                                                      0,
                                                      strlen($_SESSION['sesion_pantalla']) - 1);
            }
        
            /** MRC: El funcionamiento de esta tecla es el siguiente:
             *      A) La primera vez que pulsas (RECALL) --> escribe en pantalla el valor
             *      guardado en memoria.
             * 
             *      B) La segunda vez que pulsas la tecla (CLEAR) --> limpia el valor que
             *      está almacenado en memoria.
             */
            private function mrc() {
                if (isset($_SESSION['sesion_memoria']))
                    $_SESSION['sesion_pantalla'] = $_SESSION['sesion_memoria'];
            }
        
            // M-: Resta el valor que está guardado en memoria con el que aparece en pantalla
            private function m_menos() {
                $this->opera_en_memoria('-');
            }
        
            // M+: Suma el valor que está guardado en memoria con el que aparece en pantalla
            private function m_mas() {
                $this->opera_en_memoria('+');
            }

            private function unary_operation($function) {
                if (isset($_SESSION['sesion_pantalla']))
                    try {
                        $expresion = $function($_SESSION['sesion_pantalla']);
                        $_SESSION['sesion_pantalla'] = eval("return $expresion ;"); 
                    } catch (Error $e) {
                        $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    }
            }
        
            private function opera_en_memoria($operador) {
                try {
                    $memoria = $_SESSION['sesion_memoria'];
                    $pantalla = $_SESSION['sesion_pantalla'];
                    $_SESSION['sesion_memoria'] = eval("return $memoria"
                                                             ."$operador"
                                                             ."$pantalla ;");
                } catch (Exception $e) {
                    $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    $this->borrar();
                } catch(ParseError $p){
                    $_SESSION['sesion_pantalla'] = 'SYNTAX ERROR';
                    $this->borrar();
                }
            }

        }

        $calculadora = new CalculadoraMilan();

        $pantalla = $_SESSION['sesion_pantalla'];

        echo "
        <form action='#' method='post'>
            <h2>nata by MILAN</h2>
            <label for='pantalla'>Pantalla:</label>
            <input id='pantalla' value='$pantalla' type='text' disabled/>
           
            <input type='submit' value='C' name='borrar' />
            <input type='submit' value='CE' name='borrarUltimoDig' />
            <input type='submit' value='±' name='masMenos' />
            <input type='submit' value='√' name='raiz' />
            <input type='submit' value='%' name='modulo' />
           
            <input type='submit' value='7' name='7' />
            <input type='submit' value='8' name='8' />
            <input type='submit' value='9' name='9' />
            <input type='submit' value='×' name='producto' />
            <input type='submit' value='÷' name='division' />
           
            <input type='submit' value='4' name='4' />
            <input type='submit' value='5' name='5' />
            <input type='submit' value='6' name='6' />
            <input type='submit' value='-' name='resta' />
            <input type='submit' value='mrc' name='mrc' />
           
            <input type='submit' value='1' name='1' />
            <input type='submit' value='2' name='2' />
            <input type='submit' value='3' name='3' />
            <input type='submit' value='+' name='suma' />
            <input type='submit' value='m-' name='m-' />
           
            <input type='submit' value='0' name='0' />
            <input type='submit' value='.' name='punto' />
            <input type='submit' value='=' name='igual' />
            <input type='submit' value='m+' name='m+' />
           </form>";
    ?>
</body>
</html>