<div class="box box-info padding-1">
    <div class="box-body">
        <div class="form-group">
            <label for="name">Nombre Completo</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" placeholder="Nombre Completo">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input id="password" name="password" type="password"
                class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                placeholder="{{ $user->exists ? 'Dejar en blanco para conservarla' : 'Mínimo 8 caracteres' }}">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="city">Regional</label>
            <select id="city" name="city" class="form-control{{ $errors->has('city') ? ' is-invalid' : '' }}">
                <option value="">Seleccione la Regional</option>
                @foreach (['LA PAZ', 'COCHABAMBA', 'SANTA CRUZ', 'ORURO', 'POTOSI', 'TARIJA', 'SUCRE', 'BENI', 'PANDO'] as $city)
                    <option value="{{ $city }}" @selected(old('city', $user->city) === $city)>{{ $city }}</option>
                @endforeach
            </select>
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
            <label for="ci">Carnet de Identidad</label>
            <input id="ci" name="ci" type="text" value="{{ old('ci', $user->ci) }}"
                class="form-control{{ $errors->has('ci') ? ' is-invalid' : '' }}" placeholder="Carnet de Identidad">
            @error('ci')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="box-footer mt20">
        <button type="submit" class="btn btn-primary">{{ __('Listo') }}</button>
    </div>
</div>
