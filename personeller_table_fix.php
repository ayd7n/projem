<div class="card">
    <div class="card-header">
        <h2>Personel Listesi</h2>
    </div>
    <div class="card-body">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>İşlemler</th>
                        <th>Ad Soyad</th>
                        <th>Pozisyon</th>
                        <th>Departman</th>
                        <th>Telefon</th>
                        <th>İşe Giriş</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees_result->num_rows > 0): ?>
                        <?php while ($employee = $employees_result->fetch_assoc()): ?>
                            <?php $is_protected_row = ($employee['ad_soyad'] === PROTECTED_USER_NAME); ?>
                            <tr>
                                <td class="actions">
                                    <a href="personeller.php?edit=<?php echo $employee['personel_id']; ?>" class="btn btn-primary <?php if($is_protected_row) echo 'disabled'; ?>" title="<?php if($is_protected_row) echo 'Bu kullanıcı düzenlenemez'; ?>"><i class="fas fa-edit"></i></a>
                                    <form method="POST" onsubmit="if(<?php echo $is_protected_row ? 'true' : 'false'; ?>) { alert('<?php echo PROTECTED_USER_NAME; ?> kaydı silinemez.'); return false; } return confirm('Bu personeli silmek istediğinizden emin misiniz?');">
                                        <input type="hidden" name="personel_id" value="<?php echo $employee['personel_id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger" <?php if($is_protected_row) echo 'disabled'; ?>" title="<?php if($is_protected_row) echo 'Bu kullanıcı silinemez'; ?>"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                                <td><strong><?php echo htmlspecialchars($employee['ad_soyad']); ?></strong></td>
                                <td><?php echo htmlspecialchars($employee['pozisyon']); ?></td>
                                <td><?php echo htmlspecialchars($employee['departman']); ?></td>
                                <td><?php echo htmlspecialchars($employee['telefon']); ?></td>
                                <td><?php echo date("d.m.Y", strtotime($employee['ise_giris_tarihi'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px;">Henüz kayıtlı personel bulunmuyor.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>