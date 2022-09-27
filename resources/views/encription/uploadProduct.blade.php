<form action="{{ route('upload.product') }}" method="POST" enctype="multipart/form-data">
  @csrf
  <input type="file" name="userFile">
  <button>Cargar</button>
</form>