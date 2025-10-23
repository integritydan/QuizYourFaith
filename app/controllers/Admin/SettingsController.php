public function updateLogo(){
    if(!empty($_FILES['logo']['name'])){
        $ext=pathinfo($_FILES['logo']['name'],PATHINFO_EXTENSION);
        $path='/uploads/logo/'.uniqid().'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'],ROOT.$path);
        DB::table('settings')->update(['logo_path'=>$path]);
    }
    redirect('/admin/settings');
}
