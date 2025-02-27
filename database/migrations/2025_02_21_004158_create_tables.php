<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('detalle_venta_repartidor');
        Schema::dropIfExists('ventas_repartidor');
        Schema::dropIfExists('repartidores');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('estado_pedido');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('detalle_producion');
        Schema::dropIfExists('produccion');
        Schema::dropIfExists('maquinas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('user_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sucursales');

        Schema::create('sucursales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 256);
            $table->string('direccion', 256);
            $table->string('telefono', 20);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->constrained('sucursales')->onDelete('cascade');
            $table->string('name', 256);
            $table->string('email', 191)->unique();
            $table->string('password', 256);
            $table->string('rol', 20);
            $table->timestamps();
        });

        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->string('token', 256);
            $table->boolean('is_active');
            $table->dateTime('expires_at');
        });

        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->string('nombre', 256);
            $table->decimal('cantidad', 8, 3);
            $table->string('unidad_medicion', 20);
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('total', 8, 2);
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->string('nombre', 256);
            $table->decimal('cantidad', 8, 3);
            $table->string('unidad_medicion', 20);
        });

        Schema::create('maquinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 256);
            $table->string('materia_prima', 256);
            $table->string('ubicacion', 256);
            $table->timestamps();
        });

        Schema::create('produccion', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->foreignId('id_maquina')->nullable()->constrained('maquinas')->onDelete('set null');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('detalle_produccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_produccion')->constrained('produccion')->onDelete('cascade');
            $table->string('insumo', 256);
            $table->decimal('cantidad_insumo', 8, 3);
            $table->string('producto', 256);
            $table->decimal('cantidad_producto', 8, 3);
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->string('nombre', 256);
            $table->string('direccion', 256);
            $table->string('telefono', 20);
            $table->timestamps();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->foreignId('id_cliente')->nullable()->constrained('clientes')->onDelete('set null');
            $table->string('tipo_pago', 20);
            $table->decimal('total', 8, 2);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create('detalle_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pedido')->constrained('pedidos')->onDelete('cascade');
            $table->string('producto', 256);
            $table->decimal('cantidad', 8, 3);
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 8, 2);
        });

        Schema::create('estado_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pedido')->constrained('pedidos')->onDelete('cascade');
            $table->string('estado', 256);
            $table->decimal('saldo_pendiente', 8, 2);
            $table->decimal('saldo_abonado', 8, 2);
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 256);
            $table->string('telefono', 20);
            $table->timestamps();
        });

        // Crear tabla repartidores
        Schema::create('repartidores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_sucursal')->nullable()->constrained('sucursales')->onDelete('set null');
            $table->string('nombre', 256);
            $table->string('direccion', 256);
            $table->string('telefono', 256);
            $table->string('motocicleta_placas', 256);
            $table->timestamps(); 
        });

        // Crear tabla ventas_repartidor
        Schema::create('ventas_repartidor', function (Blueprint $table) {
            $table->id();
            $table->integer('id_sucursal');
            $table->foreignId('id_repartidor')->nullable()->constrained('repartidores')->onDelete('set null');
            $table->decimal('total', 8, 2);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); 
        });

        // Crear tabla detalle_venta_repartidor
        Schema::create('detalle_venta_repartidor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta_repartidor')->constrained('ventas_repartidor')->onDelete('cascade');
            $table->string('producto', 256);
            $table->decimal('cantidad', 8, 3);
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 8, 2);
        });
        
        DB::unprepared("
            CREATE TRIGGER update_estado_on_saldo_pendiente_zero
            BEFORE UPDATE ON estado_pedido
            FOR EACH ROW
            BEGIN
                IF NEW.saldo_pendiente = 0 THEN
                    SET NEW.estado = 'Cuenta pagada';
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_venta_repartidor');
        Schema::dropIfExists('ventas_repartidor');
        Schema::dropIfExists('repartidores');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('estado_pedido');
        Schema::dropIfExists('detalle_pedido');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('detalle_producion');
        Schema::dropIfExists('produccion');
        Schema::dropIfExists('maquinas');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('user_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sucursales');

        
        DB::unprepared("DROP TRIGGER IF EXISTS update_estado_on_saldo_pendiente_zero");
        
    }
}
